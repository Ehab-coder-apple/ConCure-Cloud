<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\DentalChart;
use App\Models\DentalTreatment;
use App\Models\Patient;
use App\Models\PatientDental;
use App\Models\PatientVisit;
use App\Models\User;
use App\Models\VisitHpi;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PatientDentalModulePresentationTest extends TestCase
{
    public function test_dental_profile_labels_are_human_readable(): void
    {
        $profile = new PatientDental([
            'oral_hygiene' => 'fair',
            'smoking_status' => 'former',
        ]);

        $this->assertSame('Fair', $profile->oral_hygiene_label);
        $this->assertSame('Former', $profile->smoking_status_label);
    }

    public function test_full_dental_page_renders_summary_and_existing_dental_links(): void
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        $patient = new Patient([
            'patient_id' => 'P-2001',
            'first_name' => 'Adam',
            'last_name' => 'Nabil',
        ]);
        $patient->id = 2;
        $patient->exists = true;
        $patient->setAttribute('dental_charts_count', 3);
        $patient->setAttribute('dental_treatments_count', 2);
        $patient->setAttribute('dental_images_count', 1);
        $patient->setRelation('clinic', new Clinic(['name' => 'Dental Clinic']));

        $dentalProfile = new PatientDental([
            'oral_hygiene' => 'good',
            'smoking_status' => 'current',
            'bruxism' => true,
            'notes' => 'Needs night guard discussion.',
        ]);

        $chart = new DentalChart([
            'chart_type' => 'adult',
            'general_notes' => 'Needs restoration follow-up.',
            'created_at' => Carbon::now()->subDays(10),
        ]);

        $treatment = new DentalTreatment([
            'procedure_name' => 'Composite Filling',
            'status' => 'completed',
            'diagnosis' => 'Dental caries',
            'notes' => 'Posterior molar restored.',
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $visit = new PatientVisit([
            'visit_date' => Carbon::now()->subDay(),
            'visit_type' => 'consultation',
            'notes' => 'Jaw pain while chewing.',
        ]);
        $visit->id = 33;
        $visit->setRelation('hpi', new VisitHpi([
            'chief_complaint' => 'Toothache',
        ]));

        $html = view('patients.dental.show', [
            'patient' => $patient,
            'dentalProfile' => $dentalProfile,
            'latestDentalChart' => $chart,
            'dentalLastVisitLabel' => 'Apr 01, 2026',
            'recentDentalCharts' => collect([$chart]),
            'recentDentalTreatments' => collect([$treatment]),
            'recentVisits' => collect([$visit]),
        ])->render();

        $this->assertStringContainsString('Dental Module', $html);
        $this->assertStringContainsString('Oral Hygiene Status', $html);
        $this->assertStringContainsString('Needs night guard discussion.', $html);
        $this->assertStringContainsString('Procedure History', $html);
        $this->assertStringContainsString('Composite Filling', $html);
        $this->assertStringContainsString('Linked Visits &amp; Dental Notes Context', $html);
    }

    public function test_dental_history_view_renders_existing_chart_timeline(): void
    {
        View::share('primaryColor', '#008080');
        View::share('errors', new ViewErrorBag());

        $patient = new Patient([
            'patient_id' => 'P-2002',
            'first_name' => 'Lina',
            'last_name' => 'Maher',
        ]);
        $patient->id = 6;
        $patient->exists = true;

        $creator = new User([
            'first_name' => 'Dina',
            'last_name' => 'Dentist',
            'role' => 'doctor',
        ]);

        $chart = new DentalChart([
            'chart_type' => 'adult',
            'general_notes' => 'Routine cleaning and restoration review.',
            'created_at' => Carbon::now()->subDays(3),
        ]);
        $chart->id = 14;
        $chart->setRelation('creator', $creator);
        $chart->setRelation('toothRecords', new EloquentCollection([new \App\Models\DentalToothRecord(), new \App\Models\DentalToothRecord()]));
        $chart->setRelation('treatments', new EloquentCollection([
            new DentalTreatment(['procedure_name' => 'Scaling']),
            new DentalTreatment(['procedure_name' => 'Composite Filling']),
        ]));

        $html = view('dental.charts.history', [
            'patient' => $patient,
            'charts' => collect([$chart]),
        ])->render();

        $this->assertStringContainsString('Dental History', $html);
        $this->assertStringContainsString('Routine cleaning and restoration review.', $html);
        $this->assertStringContainsString('Scaling, Composite Filling', $html);
        $this->assertStringContainsString('View Chart', $html);
    }
}