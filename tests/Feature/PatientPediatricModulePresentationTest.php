<?php

namespace Tests\Feature;

use App\Models\GrowthMeasurement;
use App\Models\Patient;
use App\Models\PatientDental;
use App\Models\PatientEnt;
use App\Models\PatientPediatric;
use App\Models\PatientVaccination;
use App\Models\Vaccine;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PatientPediatricModulePresentationTest extends TestCase
{
    public function test_pediatric_module_is_active_by_default_for_children_under_sixteen(): void
    {
        $patient = new Patient([
            'date_of_birth' => Carbon::now()->subYears(10)->toDateString(),
        ]);
        $patient->setRelation('activeModules', new EloquentCollection());

        $activeModules = $patient->active_profile_modules->pluck('key');

        $this->assertTrue($activeModules->contains('pediatric'));
    }

    public function test_pediatric_profile_model_classifies_lbw_and_preterm(): void
    {
        $profile = new PatientPediatric([
            'birth_weight' => 2200,
            'gestational_age' => 35,
        ]);

        $this->assertTrue($profile->is_low_birth_weight);
        $this->assertTrue($profile->is_preterm);
        $this->assertSame('LBW • Preterm', $profile->growth_status_label);
    }

    public function test_profile_hub_partial_renders_pediatric_summary(): void
    {
        $patient = new Patient([
            'patient_id' => 'P-4001',
            'first_name' => 'Yousef',
            'last_name' => 'Ali',
            'date_of_birth' => Carbon::now()->subYears(5)->toDateString(),
        ]);
        $patient->id = 7;
        $patient->exists = true;
        $patient->setAttribute('dental_last_visit_label', 'Apr 01, 2026');
        $patient->setAttribute('ent_issue_count', 0);
        $patient->setAttribute('ent_dizziness_label', 'No');
        $patient->setAttribute('ent_file_count', 0);
        $patient->setAttribute('growth_measurements_count', 0);
        $patient->setAttribute('pediatric_growth_status_label', 'LBW • Preterm');
        $patient->setAttribute('vaccinations_count', 0);

        $pediatricProfile = new PatientPediatric([
            'birth_weight' => 2300,
            'gestational_age' => 36,
            'vaccination_status' => 'delayed',
        ]);

        $html = view('patients.partials.profile-hub', [
            'patient' => $patient,
            'medicalOverview' => new \App\Models\PatientMedicalOverview(),
            'activeProfileModules' => collect([['key' => 'pediatric', 'label' => 'Pediatric', 'icon' => 'fas fa-baby', 'description' => 'Pediatric module']]),
            'availableProfileModules' => collect(),
            'currentMedications' => collect(),
            'pastMedications' => collect(),
            'recentVisits' => collect(),
            'legacyProfileHpi' => null,
            'dentalProfile' => new PatientDental(['smoking_status' => 'unknown', 'bruxism' => false]),
            'dentalLastVisitLabel' => 'Apr 01, 2026',
            'entProfile' => new PatientEnt(['dizziness' => false]),
            'pediatricProfile' => $pediatricProfile,
        ])->render();

        $this->assertStringContainsString('Pediatric', $html);
        $this->assertStringContainsString('Birth Weight', $html);
        $this->assertStringContainsString('2300', $html);
        $this->assertStringContainsString('Open Full Pediatric Module', $html);
    }

    public function test_full_pediatric_page_renders_child_summary_and_growth_context(): void
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        $patient = new Patient([
            'patient_id' => 'P-4002',
            'first_name' => 'Lina',
            'last_name' => 'Omar',
            'date_of_birth' => Carbon::now()->subYears(4)->toDateString(),
        ]);
        $patient->id = 8;
        $patient->exists = true;
        $patient->setAttribute('growth_measurements_count', 2);
        $patient->setAttribute('vaccinations_count', 3);

        $pediatricProfile = new PatientPediatric([
            'birth_weight' => 2400,
            'gestational_age' => 36,
            'feeding_type' => 'mixed',
            'vaccination_status' => 'up_to_date',
            'notes' => 'Monitor catch-up growth.',
        ]);

        $measurement = new GrowthMeasurement([
            'measurement_date' => Carbon::now()->subDays(5),
            'weight_kg' => 16.2,
            'length_height_cm' => 102.4,
        ]);

        $vaccine = new Vaccine(['global_name' => 'MMR']);
        $vaccination = new PatientVaccination([
            'dose_number' => 1,
            'status' => 'on_time',
        ]);
        $vaccination->setRelation('vaccine', $vaccine);

        $html = view('patients.pediatric.show', [
            'patient' => $patient,
            'pediatricProfile' => $pediatricProfile,
            'latestGrowthMeasurement' => $measurement,
            'recentGrowthMeasurements' => collect([$measurement]),
            'recentVaccinations' => collect([$vaccination]),
        ])->render();

        $this->assertStringContainsString('Pediatric Module', $html);
        $this->assertStringContainsString('Growth Status', $html);
        $this->assertStringContainsString('LBW', $html);
        $this->assertStringContainsString('MMR', $html);
        $this->assertStringContainsString('Growth Chart', $html);
    }
}