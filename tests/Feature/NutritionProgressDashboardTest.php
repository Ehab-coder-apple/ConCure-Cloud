<?php

namespace Tests\Feature;

use App\Models\NutritionGoal;
use App\Models\NutritionProgressMeasurement;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Verifies the Nutrition Progress Dashboard's "Add Measurement" and
 * "Set Goals" forms use absolute Kg/L units for Fat, Muscle, and Body
 * Water (instead of %), expose a direct WHR entry field, and expose a
 * new Mineral (Kg) field — without touching auth/migrations, following
 * the same in-memory rendering approach as NutritionShowUiTest.
 */
class NutritionProgressDashboardTest extends TestCase
{
    private function renderDashboard(Patient $patient, $measurements, ?NutritionGoal $goal): string
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        return view('nutrition.progress-dashboard', [
            'patients' => new EloquentCollection([$patient]),
            'selectedPatient' => $patient,
            'measurements' => $measurements,
            'goal' => $goal,
        ])->render();
    }

    private function makePatient(): Patient
    {
        $patient = new Patient(['patient_id' => 'P-9001', 'first_name' => 'Omar', 'last_name' => 'Nasser']);
        $patient->id = 701;
        $patient->exists = true;

        return $patient;
    }

    public function test_measurement_and_goal_forms_use_kg_and_litre_inputs_instead_of_percentages(): void
    {
        $html = $this->renderDashboard($this->makePatient(), new EloquentCollection([]), null);

        // Add Measurement modal: Fat/Muscle in Kg, Body Water in Litres.
        $this->assertStringContainsString('name="fat_kg"', $html);
        $this->assertStringContainsString('name="muscle_kg"', $html);
        $this->assertStringContainsString('name="body_water_liters"', $html);
        $this->assertStringNotContainsString('name="fat_percentage"', $html);
        $this->assertStringNotContainsString('name="muscle_percentage"', $html);
        $this->assertStringNotContainsString('name="body_water_percentage"', $html);

        // Set Goal modal: same absolute-unit targets.
        $this->assertStringContainsString('name="target_fat_kg"', $html);
        $this->assertStringContainsString('name="target_muscle_kg"', $html);
        $this->assertStringContainsString('name="target_body_water_liters"', $html);
        $this->assertStringNotContainsString('name="target_fat_percentage"', $html);
        $this->assertStringNotContainsString('name="target_muscle_percentage"', $html);
        $this->assertStringNotContainsString('name="target_body_water_percentage"', $html);
    }

    public function test_direct_whr_field_is_present_in_both_forms(): void
    {
        $html = $this->renderDashboard($this->makePatient(), new EloquentCollection([]), null);

        $this->assertStringContainsString('name="whr_direct"', $html);
        $this->assertStringContainsString('name="target_whr"', $html);
    }

    public function test_mineral_kg_field_is_present_in_both_forms(): void
    {
        $html = $this->renderDashboard($this->makePatient(), new EloquentCollection([]), null);

        $this->assertStringContainsString('name="mineral_kg"', $html);
        $this->assertStringContainsString('name="target_mineral_kg"', $html);
    }

    public function test_measurement_history_table_shows_kg_and_litre_columns(): void
    {
        $measurement = new NutritionProgressMeasurement([
            'measurement_date' => now(),
            'fat_kg' => 18.5,
            'muscle_kg' => 32.0,
            'mineral_kg' => 3.4,
            'body_water_liters' => 40.2,
            'waist_to_hip_ratio' => 0.91,
        ]);
        $measurement->id = 1;
        $measurement->exists = true;

        $html = $this->renderDashboard($this->makePatient(), new EloquentCollection([$measurement]), null);

        $this->assertStringContainsString('Fat (kg)', $html);
        $this->assertStringContainsString('Muscle (kg)', $html);
        $this->assertStringContainsString('Mineral (kg)', $html);
        $this->assertStringContainsString('Water (L)', $html);
        $this->assertStringContainsString('18.5', $html);
        $this->assertStringContainsString('40.2', $html);
    }

    public function test_whr_direct_entry_wins_over_waist_hip_calculation(): void
    {
        $measurement = new NutritionProgressMeasurement([
            'waist_cm' => 90,
            'hip_cm' => 100,
            'whr_direct' => 0.77,
        ]);
        $measurement->applyEffectiveWhr();

        $this->assertEquals(0.77, (float) $measurement->waist_to_hip_ratio);
    }

    public function test_whr_is_calculated_from_waist_and_hip_when_no_direct_value_given(): void
    {
        $measurement = new NutritionProgressMeasurement([
            'waist_cm' => 90,
            'hip_cm' => 100,
        ]);
        $measurement->applyEffectiveWhr();

        $this->assertEquals(0.9, (float) $measurement->waist_to_hip_ratio);
    }

}
