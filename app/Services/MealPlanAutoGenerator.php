<?php

namespace App\Services;

use App\Models\Food;

class MealPlanAutoGenerator
{
    // Default distribution of daily calories/macros per meal
    private array $mealSplits = [
        'breakfast' => 0.25,
        'lunch'     => 0.35,
        'dinner'    => 0.30,
        'snacks'    => 0.10,
    ];

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
        $foods = Food::query()
            ->where('is_active', true)
            ->limit(500)
            ->get(['id','name','calories','protein','carbohydrates','fat','serving_weight','name_translations','name_ar','name_ku']);

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

        // Rank by macro density (per 100g)
        $proteinDense = (clone $foods)->sortByDesc(fn($f) => (float)$f->protein);
        $carbDense    = (clone $foods)->sortByDesc(fn($f) => (float)$f->carbohydrates);
        $fatDense     = (clone $foods)->sortByDesc(fn($f) => (float)$f->fat);
        $balanced     = (clone $foods)->sortByDesc(function($f){
            // simple score favoring balanced items
            $p = (float)$f->protein; $c=(float)$f->carbohydrates; $fa=(float)$f->fat; $cal=(float)$f->calories;
            return $p*0.4 + $c*0.35 + $fa*0.25 + ($cal>0?50:0);
        });

        $plan = [ 'breakfast'=>[], 'lunch'=>[], 'dinner'=>[], 'snacks'=>[] ];

        foreach ($this->mealSplits as $meal => $ratio) {
            $mealCal = max(300, round($cal * $ratio));
            $mealP   = max(10,  round($p   * $ratio));
            $mealC   = max(20,  round($c   * $ratio));
            $mealF   = max(5,   round($f   * $ratio));

            $option = [
                'option_number' => 1,
                'option_description' => 'Auto suggestion',
                'foods' => [],
                'total_calories' => 0,
                'total_protein'  => 0,
                'total_carbs'    => 0,
                'total_fat'      => 0,
            ];

            $option = $this->addFromList($option, $proteinDense, 'protein', $mealP, $language);
            $option = $this->addFromList($option, $carbDense, 'carbohydrates', $mealC, $language);
            $option = $this->addFromList($option, $fatDense, 'fat', $mealF, $language);

            // If still under calories by >10%, add a balanced filler
            $tries = 0;
            while ($option['total_calories'] < $mealCal * 0.9 && $tries < 4) {
                $tries++;
                $option = $this->addFromList($option, $balanced, 'calories', ($mealCal - $option['total_calories'])/9 /*approx*/, $language, true);
            }

            $plan[$meal][] = $option;
        }

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
            $add = [
                'food_id' => $food->id,
                'food_name' => $food->name,
                'displayName' => $this->translatedName($food, $language),
                'quantity' => round($gramsNeeded, 0),
                'unit' => 'g',
            ];

            $option['foods'][] = $add;
            $option['total_calories'] += $per100['calories'] * $mult;
            $option['total_protein']  += $per100['protein'] * $mult;
            $option['total_carbs']    += $per100['carbohydrates'] * $mult;
            $option['total_fat']      += $per100['fat'] * $mult;

            $added++;
            if ($added >= 1) break; // take one item from each list per pass
        }
        return $option;
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

