<?php

namespace Tests\Unit;

use App\Models\Clinic;
use App\Models\DietPlan;
use App\Models\DietPlanMeal;
use App\Models\DietPlanMealFood;
use App\Models\Food;
use App\Models\FoodGroup;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Verifies that DietPlanMealFood can still resolve nutrition data (calories,
 * protein, carbs, fat) by matching food_name against the Food database when
 * food_id was never linked (e.g. custom/auto-suggested meal entries).
 *
 * Builds only the tables this feature touches directly against the sqlite
 * test connection, rather than running the full migration history, since
 * this repository's older migrations have pre-existing issues unrelated to
 * this feature that prevent a full fresh migration in this environment.
 */
class DietPlanMealFoodNutritionFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
        $this->buildMinimalSchema();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function buildMinimalSchema(): void
    {
        if (!Schema::hasTable('clinics')) {
            Schema::create('clinics', function ($table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('patients')) {
            Schema::create('patients', function ($table) {
                $table->id();
                $table->unsignedBigInteger('clinic_id');
                $table->string('patient_id')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('food_groups')) {
            Schema::create('food_groups', function ($table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('foods')) {
            Schema::create('foods', function ($table) {
                $table->id();
                $table->string('name');
                $table->json('name_translations')->nullable();
                $table->unsignedBigInteger('food_group_id')->nullable();
                $table->text('description')->nullable();
                $table->decimal('calories', 8, 2)->default(0);
                $table->decimal('protein', 8, 2)->default(0);
                $table->decimal('carbohydrates', 8, 2)->default(0);
                $table->decimal('fat', 8, 2)->default(0);
                $table->decimal('fiber', 8, 2)->default(0);
                $table->string('serving_size')->nullable();
                $table->decimal('serving_weight', 8, 2)->nullable();
                $table->decimal('grams_per_piece', 8, 2)->nullable();
                $table->boolean('is_custom')->default(false);
                $table->unsignedBigInteger('clinic_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('diet_plans')) {
            Schema::create('diet_plans', function ($table) {
                $table->id();
                $table->string('plan_number')->nullable();
                $table->unsignedBigInteger('patient_id');
                $table->string('title')->nullable();
                $table->string('goal')->nullable();
                $table->string('status')->default('active');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->integer('duration_days')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('diet_plan_meals')) {
            Schema::create('diet_plan_meals', function ($table) {
                $table->id();
                $table->unsignedBigInteger('diet_plan_id');
                $table->integer('day_number')->nullable();
                $table->string('meal_type');
                $table->integer('option_number')->default(1);
                $table->boolean('is_option_based')->default(false);
                $table->string('option_description')->nullable();
                $table->string('meal_name')->nullable();
                $table->text('instructions')->nullable();
                $table->time('suggested_time')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('diet_plan_meal_foods')) {
            Schema::create('diet_plan_meal_foods', function ($table) {
                $table->id();
                $table->unsignedBigInteger('diet_plan_meal_id');
                $table->unsignedBigInteger('food_id')->nullable();
                $table->string('food_name');
                $table->decimal('quantity', 8, 2);
                $table->string('unit')->default('g');
                $table->text('preparation_notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_calories_resolve_by_name_when_food_id_is_missing(): void
    {
        $clinic = Clinic::create(['name' => 'Test Clinic']);
        $patient = Patient::create(['clinic_id' => $clinic->id, 'patient_id' => 'P-1', 'first_name' => 'A', 'last_name' => 'B']);
        $foodGroup = FoodGroup::create(['name' => 'Meat']);

        // Real Food record exists in the database...
        $chickenKibbeh = Food::create([
            'name' => 'Chicken kibbeh',
            'name_translations' => [],
            'food_group_id' => $foodGroup->id,
            'calories' => 313,
            'protein' => 20,
            'carbohydrates' => 10,
            'fat' => 15,
            'is_custom' => false,
            'is_active' => true,
        ]);

        $dietPlan = DietPlan::create([
            'plan_number' => 'DIET-1',
            'patient_id' => $patient->id,
            'title' => 'Plan',
            'goal' => 'weight_loss',
            'status' => 'active',
        ]);

        $meal = DietPlanMeal::create([
            'diet_plan_id' => $dietPlan->id,
            'meal_type' => 'lunch',
            'option_number' => 1,
            'is_option_based' => true,
            'option_description' => 'Chicken Kibbeh',
        ]);

        // ...but this meal food entry was saved WITHOUT a food_id (as with
        // auto-suggested/typed entries), only a matching food_name.
        $mealFood = DietPlanMealFood::create([
            'diet_plan_meal_id' => $meal->id,
            'food_id' => null,
            'food_name' => 'Chicken kibbeh',
            'quantity' => 43,
            'unit' => 'g',
        ]);

        $resolved = $mealFood->resolved_food;
        $this->assertNotNull($resolved);
        $this->assertEquals($chickenKibbeh->id, $resolved->id);

        // 43g of a 313 cal/100g food => 134.59 cal
        $this->assertEqualsWithDelta(313 * 0.43, $mealFood->calories, 0.5);
        $this->assertEqualsWithDelta(20 * 0.43, $mealFood->protein, 0.5);
        $this->assertEqualsWithDelta(10 * 0.43, $mealFood->carbs, 0.5);
        $this->assertEqualsWithDelta(15 * 0.43, $mealFood->fat, 0.5);

        // Meal-level totals now reflect this fallback-resolved food.
        $meal->load('foods');
        $this->assertTrue($meal->has_nutrition_data);
        $this->assertGreaterThan(0, $meal->total_calories);
    }

    public function test_no_macro_data_when_food_name_does_not_exist_anywhere(): void
    {
        $clinic = Clinic::create(['name' => 'Test Clinic 2']);
        $patient = Patient::create(['clinic_id' => $clinic->id, 'patient_id' => 'P-2', 'first_name' => 'C', 'last_name' => 'D']);

        $dietPlan = DietPlan::create([
            'plan_number' => 'DIET-2',
            'patient_id' => $patient->id,
            'title' => 'Plan 2',
            'goal' => 'weight_loss',
            'status' => 'active',
        ]);

        $meal = DietPlanMeal::create([
            'diet_plan_id' => $dietPlan->id,
            'meal_type' => 'breakfast',
            'option_number' => 1,
            'is_option_based' => true,
            'option_description' => 'Mystery Meal',
        ]);

        DietPlanMealFood::create([
            'diet_plan_meal_id' => $meal->id,
            'food_id' => null,
            'food_name' => 'Totally Unknown Dish',
            'quantity' => 100,
            'unit' => 'g',
        ]);

        $meal->load('foods');
        $this->assertFalse($meal->has_nutrition_data);
        $this->assertSame(0.0, $meal->total_calories);
    }

    public function test_fallback_does_not_match_another_clinics_custom_food(): void
    {
        $clinicA = Clinic::create(['name' => 'Clinic A']);
        $clinicB = Clinic::create(['name' => 'Clinic B']);
        $patient = Patient::create(['clinic_id' => $clinicA->id, 'patient_id' => 'P-3', 'first_name' => 'E', 'last_name' => 'F']);
        $foodGroup = FoodGroup::create(['name' => 'Custom']);

        // Custom food belonging to a different clinic
        Food::create([
            'name' => 'Secret Family Recipe',
            'name_translations' => [],
            'food_group_id' => $foodGroup->id,
            'calories' => 500,
            'protein' => 20,
            'carbohydrates' => 20,
            'fat' => 20,
            'is_custom' => true,
            'clinic_id' => $clinicB->id,
            'is_active' => true,
        ]);

        $dietPlan = DietPlan::create([
            'plan_number' => 'DIET-3',
            'patient_id' => $patient->id,
            'title' => 'Plan 3',
            'goal' => 'weight_loss',
            'status' => 'active',
        ]);

        $meal = DietPlanMeal::create([
            'diet_plan_id' => $dietPlan->id,
            'meal_type' => 'dinner',
            'option_number' => 1,
            'is_option_based' => true,
            'option_description' => 'Option 1',
        ]);

        $mealFood = DietPlanMealFood::create([
            'diet_plan_meal_id' => $meal->id,
            'food_id' => null,
            'food_name' => 'Secret Family Recipe',
            'quantity' => 100,
            'unit' => 'g',
        ]);

        $this->assertNull($mealFood->resolved_food);
        $this->assertSame(0.0, $mealFood->calories);
    }
}
