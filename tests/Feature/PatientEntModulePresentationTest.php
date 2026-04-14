<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientDental;
use App\Models\PatientEnt;
use App\Models\PatientFile;
use App\Models\User;
use App\Models\PatientVisit;
use App\Services\PatientProfileModuleRegistry;
use App\Models\VisitHpi;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PatientEntModulePresentationTest extends TestCase
{
    public function test_ent_module_is_active_by_default_in_profile_tabs(): void
    {
        $patient = new Patient([
            'date_of_birth' => Carbon::now()->subYears(25)->toDateString(),
        ]);
        $patient->setRelation('activeModules', new EloquentCollection());

        $activeModules = $patient->active_profile_modules->pluck('key');

        $this->assertTrue($activeModules->contains('ent'));
        $this->assertTrue($activeModules->contains('dental'));
    }

    public function test_ent_module_is_hidden_when_clinic_does_not_enable_it(): void
    {
        $patient = new Patient([
            'date_of_birth' => Carbon::now()->subYears(25)->toDateString(),
        ]);
        $patient->setRelation('clinic', new Clinic([
            'name' => 'Dental Only Clinic',
            'enabled_modules' => ['dental'],
        ]));
        $patient->setRelation('activeModules', new EloquentCollection());

        $activeModules = $patient->active_profile_modules->pluck('key');

        $this->assertFalse($activeModules->contains('ent'));
    }

    public function test_ent_module_is_hidden_from_staff_without_ent_permission(): void
    {
        $user = new User([
            'role' => 'assistant',
            'permissions' => ['patients_view'],
        ]);

        $visibleModules = PatientProfileModuleRegistry::filterVisibleToUser(collect([
            ['key' => 'ent', 'label' => 'ENT'],
            ['key' => 'dental', 'label' => 'Dental'],
        ]), $user)->pluck('key');

        $this->assertFalse($visibleModules->contains('ent'));
        $this->assertTrue($visibleModules->contains('dental'));
    }

    public function test_profile_hub_partial_renders_ent_summary(): void
    {
        $patient = new Patient([
            'patient_id' => 'P-3001',
            'first_name' => 'Nour',
            'last_name' => 'Fady',
        ]);
        $patient->id = 3;
        $patient->exists = true;
        $patient->setAttribute('dental_last_visit_label', 'Apr 01, 2026');
        $patient->setAttribute('ent_issue_count', 3);
        $patient->setAttribute('ent_dizziness_label', 'Yes');
        $patient->setAttribute('ent_file_count', 2);

        $entProfile = new PatientEnt([
            'hearing_issues' => 'Mild left-sided hearing loss',
            'nasal_issues' => 'Chronic nasal obstruction',
            'throat_issues' => 'Recurrent sore throat',
            'notes' => 'Needs audiometry follow-up.',
            'dizziness' => true,
        ]);

        $html = view('patients.partials.profile-hub', [
            'patient' => $patient,
            'medicalOverview' => new \App\Models\PatientMedicalOverview(),
            'activeProfileModules' => collect([['key' => 'ent', 'label' => 'ENT', 'icon' => 'fas fa-notes-medical', 'description' => 'ENT module']]),
            'availableProfileModules' => collect(),
            'currentMedications' => collect(),
            'pastMedications' => collect(),
            'recentVisits' => collect(),
            'legacyProfileHpi' => null,
            'dentalProfile' => new PatientDental(['smoking_status' => 'unknown', 'bruxism' => false]),
            'dentalLastVisitLabel' => 'Apr 01, 2026',
            'entProfile' => $entProfile,
        ])->render();

        $this->assertStringContainsString('ENT', $html);
        $this->assertStringContainsString('Hearing Issues', $html);
        $this->assertStringContainsString('Mild left-sided hearing loss', $html);
        $this->assertStringContainsString('Open Full ENT Module', $html);
    }

    public function test_full_ent_page_renders_visit_context_and_ent_files(): void
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        $patient = new Patient([
            'patient_id' => 'P-3002',
            'first_name' => 'Mina',
            'last_name' => 'Ragab',
        ]);
        $patient->id = 4;
        $patient->exists = true;

        $entProfile = new PatientEnt([
            'hearing_issues' => 'Tinnitus',
            'nasal_issues' => 'Deviated septum symptoms',
            'throat_issues' => 'Hoarseness',
            'notes' => 'Check audiometry and CT findings.',
            'dizziness' => true,
        ]);

        $visit = new PatientVisit([
            'visit_date' => Carbon::now()->subDay(),
            'visit_type' => 'consultation',
            'notes' => 'ENT review requested after dizziness episodes.',
        ]);
        $visit->id = 44;
        $visit->setRelation('hpi', new VisitHpi([
            'chief_complaint' => 'Ear fullness',
        ]));

        $file = new PatientFile([
            'original_name' => 'audiometry.pdf',
            'file_size' => 1024,
            'category' => 'ent_audiometry',
            'file_path' => 'patients/demo/audiometry.pdf',
            'description' => 'Baseline audiometry report',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        $html = view('patients.ent.show', [
            'patient' => $patient,
            'entProfile' => $entProfile,
            'recentVisits' => collect([$visit]),
            'entFiles' => collect([$file]),
            'visitContextCount' => 1,
            'entFileCount' => 1,
        ])->render();

        $this->assertStringContainsString('ENT Module', $html);
        $this->assertStringContainsString('Upload ENT Files', $html);
        $this->assertStringContainsString('audiometry.pdf', $html);
        $this->assertStringContainsString('Ear fullness', $html);
        $this->assertStringContainsString('Shared allergies and other general medical data remain in Medical Overview', $html);
    }
}