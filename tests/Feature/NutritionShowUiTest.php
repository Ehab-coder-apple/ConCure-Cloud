<?php

namespace Tests\Feature;

use App\Models\DietPlan;
use App\Models\DietPlanMeal;
use App\Models\DietPlanMealFood;
use App\Models\Food;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class NutritionShowUiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
        // The view resolves nutrition data for meal foods without a linked
        // food_id by looking them up by name in the `foods` table, so it
        // must exist even though the diet plan/meal/patient models here are
        // otherwise plain in-memory (non-persisted) instances.
        if (!Schema::hasTable('foods')) {
            Schema::create('foods', function ($table) {
                $table->id();
                $table->string('name');
                $table->decimal('calories', 8, 2)->default(0);
                $table->decimal('protein', 8, 2)->default(0);
                $table->decimal('carbohydrates', 8, 2)->default(0);
                $table->decimal('fat', 8, 2)->default(0);
                $table->decimal('serving_weight', 8, 2)->nullable();
                $table->decimal('grams_per_piece', 8, 2)->nullable();
                $table->boolean('is_custom')->default(false);
                $table->unsignedBigInteger('clinic_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function renderShow(DietPlan $dietPlan, bool $isFlexiblePlan): string
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        return view('nutrition.show', [
            'dietPlan' => $dietPlan,
            'nutritionalTotals' => [],
            'isFlexiblePlan' => $isFlexiblePlan,
        ])->render();
    }

    public function test_flexible_plan_renders_meal_type_tabs_and_compact_option_cards(): void
    {
        $patient = new Patient(['patient_id' => 'P-7001', 'first_name' => 'Lina', 'last_name' => 'Aziz']);
        $patient->id = 501;
        $patient->exists = true;

        $dietPlan = new DietPlan([
            'title' => 'Flexible Weight Loss Plan',
            'plan_number' => 'DIET-2026-00099',
            'goal' => 'weight_loss',
            'status' => 'active',
        ]);
        $dietPlan->id = 900;
        $dietPlan->exists = true;
        $dietPlan->setAttribute('created_at', now());
        $dietPlan->setAttribute('updated_at', now());
        $dietPlan->setRelation('patient', $patient);
        $dietPlan->setRelation('doctor', null);

        $breakfastOption1 = new DietPlanMeal([
            'meal_type' => 'breakfast',
            'option_number' => 1,
            'is_option_based' => true,
            'option_description' => 'Egg & Cheese',
        ]);
        $breakfastOption1->id = 1;
        $breakfastOption1->exists = true;

        $eggFoodDb = new Food(['name' => 'Egg', 'calories' => 155, 'protein' => 13, 'carbohydrates' => 1.1, 'fat' => 11]);
        $eggFoodDb->id = 101;
        $eggFoodDb->exists = true;
        $eggFood = new DietPlanMealFood(['food_id' => 101, 'food_name' => 'Egg', 'quantity' => 186, 'unit' => 'g']);
        $eggFood->id = 11;
        $eggFood->exists = true;
        $eggFood->setRelation('food', $eggFoodDb);
        $cheeseFood = new DietPlanMealFood(['food_name' => 'Solid goat cheese', 'quantity' => 30, 'unit' => 'g']);
        $cheeseFood->id = 12;
        $cheeseFood->exists = true;
        $breakfastOption1->setRelation('foods', new EloquentCollection([$eggFood, $cheeseFood]));

        // Meal food saved WITHOUT a food_id (as with auto-suggested/typed entries), but a
        // matching Food record ('Chicken kibbeh') exists in the database by name — the
        // fallback lookup should resolve it and calculate real macros, not zeros.
        Food::create(['name' => 'Chicken kibbeh', 'calories' => 313, 'protein' => 20, 'carbohydrates' => 10, 'fat' => 15, 'is_custom' => false, 'is_active' => true]);

        $lunchOption1 = new DietPlanMeal([
            'meal_type' => 'lunch',
            'option_number' => 1,
            'is_option_based' => true,
            'option_description' => 'Chicken Kibbeh',
        ]);
        $lunchOption1->id = 2;
        $lunchOption1->exists = true;
        $chickenFood = new DietPlanMealFood(['food_name' => 'Chicken kibbeh', 'quantity' => 104, 'unit' => 'g']);
        $chickenFood->id = 21;
        $chickenFood->exists = true;
        $lunchOption1->setRelation('foods', new EloquentCollection([$chickenFood]));

        // A genuinely custom entry with no matching Food record anywhere
        // must still hide the macro row (no data to show).
        $dinnerOption1 = new DietPlanMeal([
            'meal_type' => 'dinner',
            'option_number' => 1,
            'is_option_based' => true,
            'option_description' => 'Mystery Dish',
        ]);
        $dinnerOption1->id = 4;
        $dinnerOption1->exists = true;
        $mysteryFood = new DietPlanMealFood(['food_name' => 'Totally Unknown Dish', 'quantity' => 100, 'unit' => 'g']);
        $mysteryFood->id = 41;
        $mysteryFood->exists = true;
        $dinnerOption1->setRelation('foods', new EloquentCollection([$mysteryFood]));

        $dietPlan->setRelation('meals', new EloquentCollection([$breakfastOption1, $lunchOption1, $dinnerOption1]));

        $html = $this->renderShow($dietPlan, true);

        // Tabbed navigation for meal types
        $this->assertStringContainsString('meal-type-tabs', $html);
        $this->assertStringContainsString('nav-tabs', $html);
        $this->assertStringContainsString('data-bs-toggle="tab"', $html);
        $this->assertStringContainsString('Breakfast', $html);
        $this->assertStringContainsString('Lunch', $html);

        // Compact card grid
        $this->assertStringContainsString('meal-options-grid', $html);
        $this->assertStringContainsString('meal-option-card', $html);

        // Two-column food line list (name left, qty right)
        $this->assertStringContainsString('food-line-list', $html);
        $this->assertStringContainsString('food-name', $html);
        $this->assertStringContainsString('food-qty', $html);
        $this->assertStringContainsString('Egg', $html);
        $this->assertStringContainsString('Chicken kibbeh', $html);

        // Macro summary chips appear for meals with at least one food resolvable to the Food database
        $this->assertStringContainsString('meal-option-macros', $html);

        // "Chicken kibbeh" has no food_id but IS matched by name in the Food database,
        // so its macros must be calculated and shown (not hidden as "no data").
        $lunchCardStart = strpos($html, 'Chicken Kibbeh');
        $this->assertNotFalse($lunchCardStart);
        $lunchCardEnd = strpos($html, 'meal-option-card', $lunchCardStart) ?: strlen($html);
        $lunchCardHtml = substr($html, $lunchCardStart, $lunchCardEnd - $lunchCardStart);
        $this->assertStringContainsString('meal-option-macros', $lunchCardHtml);
        // 104g @ 313 cal/100g = 325.52 => rounds to 326
        $this->assertStringContainsString('326', $lunchCardHtml);

        // A food name matching nothing in the database has no nutrition data,
        // so its macro row must NOT render misleading "0" totals.
        $dinnerCardStart = strpos($html, 'Mystery Dish');
        $this->assertNotFalse($dinnerCardStart);
        $dinnerCardEnd = strpos($html, 'meal-option-card', $dinnerCardStart) ?: strlen($html);
        $dinnerCardHtml = substr($html, $dinnerCardStart, $dinnerCardEnd - $dinnerCardStart);
        $this->assertStringNotContainsString('meal-option-macros', $dinnerCardHtml);

        // Sidebar: duplicated top-bar actions removed
        $this->assertStringNotContainsString('onclick="shareOnWhatsApp()"', $html);

        // Low-profile delete link, not a full-width danger button
        $this->assertStringContainsString('delete-plan-link', $html);
        $this->assertStringNotContainsString('btn btn-outline-danger w-100', $html);

        // "New Plan for Name" action retained
        $this->assertStringContainsString('New Plan for Name', $html);
    }

    public function test_traditional_day_based_plan_renders_day_tabs_and_compact_cards(): void
    {
        $patient = new Patient(['patient_id' => 'P-7002', 'first_name' => 'Omar', 'last_name' => 'Saleh']);
        $patient->id = 502;
        $patient->exists = true;

        $dietPlan = new DietPlan([
            'title' => 'Standard 7-Day Plan',
            'plan_number' => 'DIET-2026-00100',
            'goal' => 'maintenance',
            'status' => 'active',
        ]);
        $dietPlan->id = 901;
        $dietPlan->exists = true;
        $dietPlan->setAttribute('created_at', now());
        $dietPlan->setAttribute('updated_at', now());
        $dietPlan->setRelation('patient', $patient);
        $dietPlan->setRelation('doctor', null);

        $day1Breakfast = new DietPlanMeal([
            'day_number' => 1,
            'meal_type' => 'breakfast',
            'is_option_based' => false,
            'meal_name' => 'Morning Meal',
        ]);
        $day1Breakfast->id = 3;
        $day1Breakfast->exists = true;
        $oatsFood = new DietPlanMealFood(['food_name' => 'Oats', 'quantity' => 50, 'unit' => 'g']);
        $oatsFood->id = 31;
        $oatsFood->exists = true;
        $day1Breakfast->setRelation('foods', new EloquentCollection([$oatsFood]));

        $dietPlan->setRelation('meals', new EloquentCollection([$day1Breakfast]));

        $html = $this->renderShow($dietPlan, false);

        $this->assertStringContainsString('meal-type-tabs', $html);
        $this->assertStringContainsString('Day 1', $html);
        $this->assertStringContainsString('meal-options-grid', $html);
        $this->assertStringContainsString('meal-option-card', $html);
        $this->assertStringContainsString('Oats', $html);
    }
}
