<?php

namespace Tests\Feature;

use App\Models\DietPlan;
use App\Models\NutritionGoal;
use App\Models\NutritionProgressMeasurement;
use App\Models\Patient;
use App\Models\PatientDental;
use App\Models\PatientEnt;
use App\Models\PatientMedicalOverview;
use App\Models\PatientNutrition;
use App\Models\PatientVisit;
use App\Models\VisitHpi;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PatientNutritionModulePresentationTest extends TestCase
{
    public function test_nutrition_profile_uses_patient_bmi_calculation_rule(): void
    {
        $this->assertSame(23.53, Patient::calculateBMI(68, 170));
    }

    public function test_nutrition_module_is_active_by_default_for_patient_profile(): void
    {
        $patient = new Patient([
            'date_of_birth' => Carbon::now()->subYears(30)->toDateString(),
        ]);
        $patient->setRelation('activeModules', new EloquentCollection());

        $activeModules = $patient->active_profile_modules->pluck('key');

        $this->assertTrue($activeModules->contains('nutrition'));
    }

    public function test_profile_hub_partial_renders_nutrition_summary(): void
    {
        $patient = new Patient([
            'patient_id' => 'P-5001',
            'first_name' => 'Mira',
            'last_name' => 'Omar',
            'height' => 168,
            'weight' => 72,
            'bmi' => 25.5,
        ]);
        $patient->id = 9;
        $patient->exists = true;
        $patient->setAttribute('diet_plans_count', 2);
        $patient->setAttribute('nutrition_progress_measurements_count', 3);
        $patient->setAttribute('nutrition_last_visit_label', 'Apr 11, 2026');

        $nutritionProfile = new PatientNutrition([
            'height' => 168,
            'weight' => 72,
            'bmi' => 25.51,
            'diet_type' => 'Balanced',
            'goals' => 'Lose 4 kg over 8 weeks',
        ]);

        $html = view('patients.partials.profile-hub', [
            'patient' => $patient,
            'medicalOverview' => new PatientMedicalOverview(),
            'activeProfileModules' => collect([['key' => 'nutrition', 'label' => 'Nutrition', 'icon' => 'fas fa-apple-whole', 'description' => 'Nutrition module']]),
            'availableProfileModules' => collect(),
            'currentMedications' => collect(),
            'pastMedications' => collect(),
            'recentVisits' => collect(),
            'legacyProfileHpi' => null,
            'dentalProfile' => new PatientDental(['smoking_status' => 'unknown', 'bruxism' => false]),
            'dentalLastVisitLabel' => 'Apr 01, 2026',
            'entProfile' => new PatientEnt(['dizziness' => false]),
            'pediatricProfile' => new \App\Models\PatientPediatric(['vaccination_status' => 'unknown']),
            'nutritionProfile' => $nutritionProfile,
            'latestNutritionMeasurement' => null,
            'activeNutritionGoal' => null,
            'latestNutritionPlan' => null,
        ])->render();

        $this->assertStringContainsString('Nutrition', $html);
        $this->assertStringContainsString('Diet Type', $html);
        $this->assertStringContainsString('Balanced', $html);
        $this->assertStringContainsString('Lose 4 kg over 8 weeks', $html);
    }

    public function test_full_nutrition_page_renders_progress_and_visit_context(): void
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        $patient = new Patient([
            'patient_id' => 'P-5002',
            'first_name' => 'Huda',
            'last_name' => 'Saleh',
        ]);
        $patient->id = 10;
        $patient->exists = true;
        $patient->setAttribute('diet_plans_count', 1);
        $patient->setAttribute('nutrition_progress_measurements_count', 2);
        $patient->setAttribute('nutrition_goals_count', 1);

        $profile = new PatientNutrition([
            'height' => 165,
            'weight' => 70,
            'bmi' => 25.71,
            'diet_type' => 'Weight loss',
            'notes' => 'Focus on meal timing and hydration.',
        ]);

        $goal = new NutritionGoal([
            'target_weight' => 64,
            'target_bmi' => 23.5,
            'notes' => 'Target over 12 weeks',
            'is_active' => true,
        ]);

        $measurement = new NutritionProgressMeasurement([
            'measurement_date' => Carbon::now()->subDays(7),
            'weight_kg' => 70,
            'height_cm' => 165,
            'bmi' => 25.71,
            'notes' => 'Initial assessment',
        ]);

        $dietPlan = new DietPlan([
            'title' => 'Calorie Deficit Plan',
            'goal' => 'weight_loss',
        ]);

        $visit = new PatientVisit([
            'visit_date' => Carbon::now()->subDay(),
            'visit_type' => 'follow_up',
            'reason_for_visit' => 'Nutrition follow-up',
        ]);
        $visit->id = 44;
        $visit->setRelation('hpi', new VisitHpi([
            'chief_complaint' => 'Follow-up review',
        ]));

        $html = view('patients.nutrition.show', [
            'patient' => $patient,
            'nutritionProfile' => $profile,
            'latestMeasurement' => $measurement,
            'recentMeasurements' => collect([$measurement]),
            'activeGoal' => $goal,
            'recentDietPlans' => collect([$dietPlan]),
            'recentVisits' => collect([$visit]),
            'lastVisitLabel' => 'Apr 11, 2026',
        ])->render();

        $this->assertStringContainsString('Nutrition Module', $html);
        $this->assertStringContainsString('Progress Over Time', $html);
        $this->assertStringContainsString('Follow-up review', $html);
        $this->assertStringContainsString('Use this module for diet type, goals, and nutrition notes only', $html);
    }
}