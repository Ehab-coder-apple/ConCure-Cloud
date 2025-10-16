<?php

namespace App\Services;

use App\Models\Food;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class MealPlanAutoGenerator
{
    // Default distribution of daily calories/macros per meal
    private array $mealSplits = [
        'breakfast' => 0.25,
        'lunch'     => 0.35,
        'dinner'    => 0.30,
        'snacks'    => 0.10,
    ];


    /**
     * Read options-per-meal from tenant (clinic) settings.
     * Supports keys: options_per_meal, nutrition_options_per_meal, meal_options_per_meal.
     * Caps to 1..6 to avoid excessive combinations.
     */
    private function getOptionsPerMeal(): int
    {
        try {
            $user = Auth::user();
            $clinicId = $user->clinic_id ?? null;
            if (!$clinicId) return 1;

            $keys = ['options_per_meal','nutrition_options_per_meal','meal_options_per_meal'];
            foreach ($keys as $key) {
                $val = DB::table('settings')
                    ->where('clinic_id', $clinicId)
                    ->where('key', $key)
                    ->value('value');
                if ($val !== null) {
                    $num = (int)$val;
                    if ($num < 1) $num = 1;
                    if ($num > 6) $num = 6;
                    return $num;
                }
            }
        } catch (\Throwable $e) {
            // Fallback silently
        }
        return 1; // default: maintain previous single-option behavior
    }
    public function generate(array $targets, string $language = 'default', array $restrictions = []): array
    {
        $cal = (float)($targets['calories'] ?? 0);
        $p   = (float)($targets['protein']  ?? 0);
        $c   = (float)($targets['carbs']    ?? 0);
        $f   = (float)($targets['fat']      ?? 0);

        if ($cal <= 0 || ($p + $c + $f) <= 0) {
            return [
                'success' => false,
                'message' => 'Missing or invalid targets for auto generation',
            ];
        }

        // Prefetch a reasonably sized candidate list once to keep it fast
        // Use get() without an explicit column list to avoid errors when optional
        // translation columns (e.g., name_ku) are not present in some deployments.
        $hasMealTypeTables = Schema::hasTable('meal_types') && Schema::hasTable('food_meal_type');
        $query = Food::query()
            ->where('is_active', true)
            ->limit(500);
        if ($hasMealTypeTables) {
            $query->with('mealTypes');
        }
        $foods = $query->get();

        // Basic dietary filtering (extendable)
        if (!empty($restrictions)) {
            $foods = $foods->filter(function($food) use ($restrictions) {
                $name = mb_strtolower($food->name);
                foreach ($restrictions as $r) {
                    $r = trim(mb_strtolower($r));
                    if ($r === 'vegetarian' || $r === 'vegan') {
                        if (preg_match('/beef|chicken|meat|fish|tuna|turkey|lamb|shrimp|pork/i', $name)) {
                            return false;
                        }
                    }
                    if ($r === 'no pork') {
                        if (preg_match('/pork|bacon|ham/i', $name)) {
                            return false;
                        }
                    }
                }
                return true;
            });
        }

        $plan = [ 'breakfast'=>[], 'lunch'=>[], 'dinner'=>[], 'snacks'=>[] ];

        foreach ($this->mealSplits as $meal => $ratio) {
            $mealCal = max(300, round($cal * $ratio));
            $mealP   = max(10,  round($p   * $ratio));
            $mealC   = max(20,  round($c   * $ratio));
            $mealF   = max(5,   round($f   * $ratio));

            // Determine meal type filter (map 'snacks' to 'snack')
            $mealTypeFilter = $meal === 'snacks' ? 'snack' : $meal;

            // Filter foods by meal type; support many-to-many relation with legacy fallback
            $mealFoods = $foods->filter(function($food) use ($mealTypeFilter, $hasMealTypeTables) {
                if ($hasMealTypeTables && $food->relationLoaded('mealTypes')) {
                    $types = $food->mealTypes->pluck('key')->all();
                    if (!empty($types)) {
                        return in_array($mealTypeFilter, $types, true);
                    }
                }
                $mt = $food->meal_type ?? 'any';
                return $mt === 'any' || $mt === $mealTypeFilter;
            });

            // Fallback: if no foods matched, use the full list to avoid empty meals
            if ($mealFoods->isEmpty()) {
                $mealFoods = $foods;
            }

            // Rank by macro density (per 100g) within the filtered set
            $proteinDense = $mealFoods->sortByDesc(fn($f) => (float)($f->protein ?? 0));
            $carbDense    = $mealFoods->sortByDesc(fn($f) => (float)($f->carbohydrates ?? 0));
            $fatDense     = $mealFoods->sortByDesc(fn($f) => (float)($f->fat ?? 0));
            $balanced     = $mealFoods->sortByDesc(function($f){
                $p = (float)($f->protein ?? 0); $c = (float)($f->carbohydrates ?? 0); $fa = (float)($f->fat ?? 0); $cal = (float)($f->calories ?? 0);
                return $p*0.4 + $c*0.35 + $fa*0.25 + ($cal>0?50:0);
            });

            // How many options per meal? Read from tenant (clinic) settings; default to 1..6
            $optionsPerMeal = $this->getOptionsPerMeal();

            // Build N options aiming at the same calorie target but with varied combinations
            for ($optNum = 1; $optNum <= $optionsPerMeal; $optNum++) {
                // Rotate lists slightly per option to encourage variety while preserving ranking bias
                $offBase = ($optNum - 1) * 2; // small step per option
                $protList = $proteinDense->slice($offBase)->concat($proteinDense->take($offBase));
                $carbList = $carbDense->slice($offBase + 1)->concat($carbDense->take($offBase + 1));
                $fatList  = $fatDense->slice($offBase + 2)->concat($fatDense->take($offBase + 2));
                $balList  = $balanced->slice($offBase + 1)->concat($balanced->take($offBase + 1));

                $option = [
                    'option_number' => $optNum,
                    'option_description' => 'Auto suggestion',
                    'foods' => [],
                    'total_calories' => 0,
                    'total_protein'  => 0,
                    'total_carbs'    => 0,
                    'total_fat'      => 0,
                ];

                $option = $this->addFromList($option, $protList, 'protein', $mealP, $language);
                $option = $this->addFromList($option, $carbList, 'carbohydrates', $mealC, $language);
                $option = $this->addFromList($option, $fatList, 'fat', $mealF, $language);

                // If still under calories by >10%, add a balanced filler
                $tries = 0;
                while ($option['total_calories'] < $mealCal * 0.9 && $tries < 4) {
                    $tries++;
                    $option = $this->addFromList($option, $balList, 'calories', ($mealCal - $option['total_calories'])/9 /*approx*/, $language, true);
                }
                // Tighten to be close to meal calorie target (reduce if we overshoot)
                $option = $this->rebalanceToCalorieTarget($option, $mealCal, 0.04); // 4% tolerance

                $plan[$meal][] = $option;
            }
        }

        // Final day-level tightening: if daily calories overshoot, scale down uniformly
        $plan = $this->rebalanceDayToCalorieTarget($plan, $cal, 0.04);

        return [
            'success' => true,
            'meal_options' => $plan,
        ];
    }


    private function addFromList(array $option, $list, string $macroKey, float $targetMacro, string $language, bool $byCalories = false): array
    {
        if ($targetMacro <= 0) return $option;

        $added = 0;
        foreach ($list as $food) {
            $per100 = [
                'calories' => (float)($food->calories ?? 0),
                'protein'  => (float)($food->protein ?? 0),
                'carbohydrates' => (float)($food->carbohydrates ?? 0),
                'fat' => (float)($food->fat ?? 0),
            ];

            $den = $byCalories ? max(1, $per100['calories']) : max(0.1, $per100[$macroKey] ?? 0.1);
            $gramsNeeded = $byCalories
                ? min(300, max(30, ($targetMacro * 100) / ($den)))
                : min(300, max(30, ($targetMacro * 100) / ($den)));

            // Skip extreme values or unrealistic items
            if ($gramsNeeded < 30 || $gramsNeeded > 300) continue;

            $mult = $gramsNeeded / 100.0;
            $cals = ($per100['calories'] ?? 0) * $mult;
            $prot = ($per100['protein'] ?? 0) * $mult;
            $carb = ($per100['carbohydrates'] ?? 0) * $mult;
            $fatg = ($per100['fat'] ?? 0) * $mult;

            $add = [
                'food_id' => $food->id,
                'food_name' => $food->name,
                'displayName' => $this->translatedName($food, $language),
                'quantity' => round($gramsNeeded, 0),
                'unit' => 'g',
                // Per-item nutrition for UI rendering
                'calories' => round($cals, 0),
                'protein'  => round($prot, 1),
                'carbs'    => round($carb, 1),
                'fat'      => round($fatg, 1),
            ];

            $option['foods'][] = $add;
            $option['total_calories'] += $cals;
            $option['total_protein']  += $prot;
            $option['total_carbs']    += $carb;
            $option['total_fat']      += $fatg;

            $added++;

            if ($added >= 1) break; // take one item from each list per pass
        }
        return $option;
    }


    /**
     * Reduce quantities gently if we overshoot calories. Keep a small tolerance.
     */
    private function rebalanceToCalorieTarget(array $option, float $mealCal, float $tolerance = 0.05): array
    {
        $upper = $mealCal * (1 + $tolerance);
        if (($option['total_calories'] ?? 0) <= $upper) {
            return $option; // already within tolerance
        }

        $diff = ($option['total_calories'] ?? 0) - $mealCal; // calories to shave
        // Walk items from last to first and reduce grams down to a 30g floor
        for ($i = count($option['foods']) - 1; $i >= 0 && $diff > 0; $i--) {


            $item = $option['foods'][$i];
            $qty  = (float)($item['quantity'] ?? 0);
            if ($qty <= 30) continue; // keep minimum practical serving

            $cal  = (float)($item['calories'] ?? 0);
            $p    = (float)($item['protein'] ?? 0);
            $c    = (float)($item['carbs'] ?? 0);
            $f    = (float)($item['fat'] ?? 0);

            $calPerGram = $qty > 0 ? max(0.1, $cal / $qty) : 0.1;
            $gramsToCut = min($qty - 30, ceil($diff / $calPerGram));
            if ($gramsToCut <= 0) continue;

            $newQty = max(30, $qty - $gramsToCut);
            $scale  = $newQty / $qty;

            // Scale item macros
            $option['foods'][$i]['quantity'] = round($newQty, 0);
            $option['foods'][$i]['calories'] = round($cal * $scale, 0);
            $option['foods'][$i]['protein']  = round($p * $scale, 1);
            $option['foods'][$i]['carbs']    = round($c * $scale, 1);
            $option['foods'][$i]['fat']      = round($f * $scale, 1);

            // Update totals
            $calCut = $cal - ($cal * $scale);
            $protCut = $p - ($p * $scale);
            $carbCut = $c - ($c * $scale);
            $fatCut  = $f - ($f * $scale);

            $option['total_calories'] -= $calCut;
            $option['total_protein']  -= $protCut;
            $option['total_carbs']    -= $carbCut;
            $option['total_fat']      -= $fatCut;

            $diff = max(0, $option['total_calories'] - $mealCal);
        }

        // Final clamp: if still slightly over, scale all items uniformly
        if ($option['total_calories'] > $upper && ($option['total_calories'] > 0)) {
            $scale = $mealCal / $option['total_calories'];


            foreach ($option['foods'] as $idx => $item) {
                $option['foods'][$idx]['quantity'] = max(30, round(($item['quantity'] ?? 0) * $scale, 0));
                $option['foods'][$idx]['calories'] = round(($item['calories'] ?? 0) * $scale, 0);
                $option['foods'][$idx]['protein']  = round(($item['protein'] ?? 0) * $scale, 1);
                $option['foods'][$idx]['carbs']    = round(($item['carbs'] ?? 0) * $scale, 1);
                $option['foods'][$idx]['fat']      = round(($item['fat'] ?? 0) * $scale, 1);
            }
            $option['total_calories'] = round($option['total_calories'] * $scale, 0);
            $option['total_protein']  = round($option['total_protein'] * $scale, 1);
            $option['total_carbs']    = round($option['total_carbs'] * $scale, 1);
            $option['total_fat']      = round($option['total_fat'] * $scale, 1);
        }

        return $option;
    }

    /**
     * If the total daily calories overshoot the target, scale all meals down uniformly.
     * When multiple options exist per meal, compute scaling based on the first option
     * of each meal and apply the same scale to all options to keep them consistent.
     */
    private function rebalanceDayToCalorieTarget(array $plan, float $dailyCal, float $tolerance = 0.04): array
    {
        $current = 0.0;
        foreach ($plan as $meal => $options) {
            if (!empty($options) && isset($options[0])) {
                $opt = $options[0];
                $current += (float)($opt['total_calories'] ?? 0);
            }
        }
        $upper = $dailyCal * (1 + $tolerance);
        if ($current <= $upper || $current <= 0) {
            return $plan; // within tolerance or nothing to scale
        }

        $scale = $dailyCal / $current;
        foreach ($plan as $meal => &$options) {
            foreach ($options as &$opt) {
                if (empty($opt['foods'])) continue;
                $opt['total_calories'] = 0;
                $opt['total_protein']  = 0;
                $opt['total_carbs']    = 0;
                $opt['total_fat']      = 0;
                foreach ($opt['foods'] as &$item) {
                    $item['quantity'] = max(30, round(($item['quantity'] ?? 0) * $scale, 0));


                    $item['calories'] = round(($item['calories'] ?? 0) * $scale, 0);
                    $item['protein']  = round(($item['protein'] ?? 0) * $scale, 1);
                    $item['carbs']    = round(($item['carbs'] ?? 0) * $scale, 1);
                    $item['fat']      = round(($item['fat'] ?? 0) * $scale, 1);

                    $opt['total_calories'] += $item['calories'];
                    $opt['total_protein']  += $item['protein'];
                    $opt['total_carbs']    += $item['carbs'];
                    $opt['total_fat']      += $item['fat'];
                }
                unset($item);
            }
            unset($opt);
        }
        unset($options);

        return $plan;
    }


    private function translatedName($food, string $language): string
    {
        if ($language === 'default' || !$language) return $food->name ?? '';

        // Support JSON translations column if present
        if (!empty($food->name_translations)) {
            $translations = is_string($food->name_translations)
                ? json_decode($food->name_translations, true)
                : $food->name_translations;
            if (is_array($translations) && !empty($translations[$language])) {
                return $translations[$language];
            }
        }
        if ($language === 'ar' && !empty($food->name_ar)) return $food->name_ar;
        if (($language === 'ku_bahdini' || $language === 'ku_sorani') && !empty($food->name_ku)) return $food->name_ku;
        return $food->name ?? '';
    }
}

