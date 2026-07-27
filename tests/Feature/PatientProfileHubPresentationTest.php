<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientDental;
use App\Models\PatientEnt;
use App\Models\PatientMedicalOverview;
use App\Models\PatientMedication;
use App\Models\PatientModule;
use App\Models\PatientPediatric;
use App\Models\PatientVisit;
use App\Models\VisitHpi;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Services\PatientProfileModuleRegistry;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PatientProfileHubPresentationTest extends TestCase
{
    public function test_patient_accessors_prefer_medical_overview_values(): void
    {
        $patient = new Patient([
            'allergies' => 'Legacy allergy',
            'chronic_illnesses' => 'Legacy chronic disease',
            'surgeries_history' => 'Legacy surgery',
            'medical_history' => 'Legacy notes',
            'is_pregnant' => false,
        ]);

        $patient->setRelation('medicalOverview', new PatientMedicalOverview([
            'allergies' => 'Peanuts',
            'chronic_diseases' => 'Diabetes',
            'surgeries' => 'Appendectomy',
            'medical_history' => 'Shared overview note',
            'flags' => ['pregnant' => true],
        ]));

        $this->assertSame('Peanuts', $patient->allergies);
        $this->assertSame('Diabetes', $patient->chronic_illnesses);
        $this->assertSame('Appendectomy', $patient->surgeries_history);
        $this->assertSame('Shared overview note', $patient->medical_history);
        $this->assertTrue($patient->is_pregnant);
        $this->assertArrayHasKey('pregnant', $patient->medical_flags);
    }

    public function test_pediatric_module_is_hidden_for_patients_aged_sixteen_or_older(): void
    {
        $patient = new Patient([
            'date_of_birth' => Carbon::now()->subYears(16)->toDateString(),
        ]);

        $eligibleModules = collect(PatientProfileModuleRegistry::eligibleModulesForPatient($patient))->pluck('key');

        $this->assertFalse($eligibleModules->contains('pediatric'));
        $this->assertTrue($eligibleModules->contains('dental'));
        $this->assertFalse($eligibleModules->contains('pediatric'));
    }

    public function test_dental_module_is_active_by_default_in_profile_tabs(): void
    {
        $patient = new Patient([
            'date_of_birth' => Carbon::now()->subYears(30)->toDateString(),
        ]);
        $patient->setRelation('activeModules', new EloquentCollection());

        $activeModules = $patient->active_profile_modules->pluck('key');
        $availableModules = $patient->available_profile_modules->pluck('key');

        $this->assertTrue($activeModules->contains('dental'));
        $this->assertTrue($activeModules->contains('nutrition'));
        $this->assertFalse($availableModules->contains('dental'));
    }

    public function test_clinic_enabled_modules_filter_patient_profile_modules(): void
    {
        $patient = new Patient([
            'date_of_birth' => Carbon::now()->subYears(12)->toDateString(),
        ]);

        $patient->setRelation('clinic', new Clinic([
            'name' => 'Dental Only Clinic',
            'enabled_modules' => ['dental'],
        ]));
        $patient->setRelation('activeModules', new EloquentCollection([
            new PatientModule(['module_name' => 'nutrition', 'is_active' => true]),
        ]));

        $this->assertSame(['dental'], $patient->active_profile_modules->pluck('key')->all());
        $this->assertSame([], $patient->available_profile_modules->pluck('key')->all());
    }

    public function test_profile_hub_partial_renders_dynamic_tabs_and_visit_hpi(): void
    {
        $patient = new Patient([
            'patient_id' => 'P-1001',
            'first_name' => 'Sara',
            'last_name' => 'Ali',
            'date_of_birth' => Carbon::now()->subYears(12)->toDateString(),
            'gender' => 'female',
            'phone' => '0100000000',
            'blood_type' => 'A+',
            'address' => 'Cairo',
            'emergency_contact_name' => 'Mona',
            'emergency_contact_phone' => '0111111111',
        ]);
        $patient->id = 1;
        $patient->exists = true;
        $patient->setAttribute('dental_charts_count', 2);
        $patient->setAttribute('dental_treatments_count', 1);
        $patient->setAttribute('dental_images_count', 0);
        $patient->setAttribute('dental_last_visit_label', 'Apr 01, 2026');
        $patient->setAttribute('growth_measurements_count', 4);
        $patient->setAttribute('pediatric_prescriptions_count', 1);
        $patient->setAttribute('vaccinations_count', 6);

        $patient->setRelation('clinic', new Clinic(['name' => 'Demo Clinic']));

        $medicalOverview = new PatientMedicalOverview([
            'allergies' => 'Penicillin',
            'chronic_diseases' => 'Asthma',
            'surgeries' => 'None',
            'medical_history' => 'Shared clinical summary',
            'current_medications_summary' => 'Uses inhaler when needed',
            'flags' => ['diabetic' => true],
        ]);

        $dentalProfile = new PatientDental([
            'oral_hygiene' => 'good',
            'smoking_status' => 'never',
            'bruxism' => true,
            'notes' => 'Brushes twice daily.',
        ]);
        $entProfile = new PatientEnt([
            'dizziness' => false,
        ]);
        $pediatricProfile = new PatientPediatric([
            'birth_weight' => 2450,
            'gestational_age' => 36,
            'vaccination_status' => 'up_to_date',
        ]);

        $currentMedication = new PatientMedication([
            'medication_name' => 'Salbutamol',
            'dosage' => '2 puffs',
            'frequency' => 'PRN',
            'route' => 'Inhaled',
            'status' => 'current',
        ]);

        $visit = new PatientVisit([
            'visit_date' => Carbon::now()->subDay(),
            'visit_type' => 'consultation',
            'reason_for_visit' => 'Cough',
        ]);
        $visit->id = 22;
        $visit->setRelation('hpi', new VisitHpi([
            'chief_complaint' => 'Persistent cough',
            'hpi_summary' => 'Dry cough for 3 days with no fever.',
        ]));

        $html = view('patients.partials.profile-hub', [
            'patient' => $patient,
            'medicalOverview' => $medicalOverview,
            'activeProfileModules' => collect([
                PatientProfileModuleRegistry::find('dental'),
                PatientProfileModuleRegistry::find('pediatric'),
            ]),
            'availableProfileModules' => collect([
                PatientProfileModuleRegistry::find('nutrition'),
            ]),
            'currentMedications' => collect([$currentMedication]),
            'pastMedications' => collect(),
            'recentVisits' => collect([$visit]),
            'legacyProfileHpi' => null,
            'dentalProfile' => $dentalProfile,
            'dentalLastVisitLabel' => 'Apr 01, 2026',
            'entProfile' => $entProfile,
            'pediatricProfile' => $pediatricProfile,
        ])->render();

        $this->assertStringContainsString('Modular Patient Profile', $html);
        $this->assertStringContainsString('Medical Overview', $html);
        $this->assertStringContainsString('Persistent cough', $html);
        $this->assertStringContainsString('Dental', $html);
        $this->assertStringContainsString('Oral Hygiene Status', $html);
        $this->assertStringContainsString('Brushes twice daily.', $html);
        $this->assertStringContainsString('Pediatric', $html);
        $this->assertStringContainsString('Birth Weight', $html);
        $this->assertStringContainsString('Add Module', $html);
    }
}