<?php

namespace Tests\Feature;

use App\Models\Clinic;
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

/**
 * Covers the "Create/Edit Detailed Nutrition Plan" (create-enhanced) page:
 * it should use the compact meal-options-grid layout (matching the plan
 * dashboard) and, when editing, correctly reconstruct calories/macros for
 * meal foods saved without a food_id by falling back to a name match
 * against the Food database (DietPlanMealFood::resolved_food).
 *
 * Builds only the tables this feature touches directly against the sqlite
 * test connection (rather than running the full migration history), since
 * this repository's older migrations have pre-existing issues unrelated to
 * this feature that prevent a full fresh migration in this environment.
 */
class NutritionCreateEnhancedUiTest extends TestCase
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

    private function renderCreateEnhanced(?DietPlan $dietPlan, $patients, $foodGroups, $selectedPatient): string
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        return view('nutrition.create-enhanced', [
            'dietPlan' => $dietPlan,
            'patients' => $patients,
            'foodGroups' => $foodGroups,
            'selectedPatient' => $selectedPatient,
        ])->render();
    }

    public function test_edit_page_uses_compact_meal_options_grid_layout(): void
    {
        $clinic = Clinic::create(['name' => 'Test Clinic']);
        $patient = Patient::create(['clinic_id' => $clinic->id, 'patient_id' => 'P-9001', 'first_name' => 'Sara', 'last_name' => 'Kamal']);

        $dietPlan = DietPlan::create([
            'plan_number' => 'DIET-2026-00200',
            'patient_id' => $patient->id,
            'title' => 'Edit Test Plan',
            'goal' => 'weight_loss',
            'status' => 'active',
        ]);
        $dietPlan->setRelation('patient', $patient);
        $dietPlan->setRelation('meals', new EloquentCollection([]));

        $html = $this->renderCreateEnhanced($dietPlan, new EloquentCollection([$patient]), new EloquentCollection([]), $patient);

        // Compact grid container classes reused from the dashboard
        $this->assertStringContainsString('meal-options-grid', $html);
        $this->assertStringContainsString('id="breakfast-options"', $html);
    }

    public function test_editing_meal_food_without_food_id_resolves_calories_by_name(): void
    {
        $clinic = Clinic::create(['name' => 'Test Clinic 2']);
        $patient = Patient::create(['clinic_id' => $clinic->id, 'patient_id' => 'P-9002', 'first_name' => 'Nour', 'last_name' => 'Aziz']);

        // A real Food record exists in the database...
        $chickenKibbeh = Food::create([
            'name' => 'Chicken kibbeh',
            'calories' => 313,
            'protein' => 20,
            'carbohydrates' => 10,
            'fat' => 15,
            'is_custom' => false,
            'is_active' => true,
        ]);

        $dietPlan = DietPlan::create([
            'plan_number' => 'DIET-2026-00201',
            'patient_id' => $patient->id,
            'title' => 'Edit Test Plan 2',
            'goal' => 'weight_loss',
            'status' => 'active',
        ]);

        $breakfastOption1 = DietPlanMeal::create([
            'diet_plan_id' => $dietPlan->id,
            'meal_type' => 'breakfast',
            'option_number' => 1,
            'is_option_based' => true,
            'option_description' => 'Chicken Kibbeh Option',
        ]);

        // Saved WITHOUT a food_id (as with auto-suggested/typed entries), but the
        // name matches a real Food record above.
        DietPlanMealFood::create([
            'diet_plan_meal_id' => $breakfastOption1->id,
            'food_id' => null,
            'food_name' => 'Chicken kibbeh',
            'quantity' => 100,
            'unit' => 'g',
        ]);

        // Reload exactly like NutritionController::editEnhanced/createEnhanced do.
        $dietPlan->load(['patient', 'meals.foods.food']);

        $controller = new \App\Http\Controllers\NutritionController();
        $backfill = new \ReflectionMethod($controller, 'backfillResolvedFoodRelations');
        $backfill->setAccessible(true);
        $backfill->invoke($controller, $dietPlan);

        $html = $this->renderCreateEnhanced($dietPlan, new EloquentCollection([$patient]), new EloquentCollection([]), $patient);

        // The backfilled `food` relation must be serialized into the JSON blob
        // the page's JS uses to reconstruct meal options on load.
        $this->assertStringContainsString('Chicken kibbeh', $html);
        $this->assertStringContainsString('313', $html); // resolved food's calories, embedded in the JSON payload
    }
}
