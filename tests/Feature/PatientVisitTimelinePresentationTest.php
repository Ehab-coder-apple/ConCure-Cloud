<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientCheckup;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PatientVisitTimelinePresentationTest extends TestCase
{
    public function test_checkup_derives_clinical_fields_from_custom_fields_and_fallbacks(): void
    {
        $checkup = new PatientCheckup([
            'custom_fields' => [
                'chief_complaint' => 'Severe sore throat',
                'diagnosis' => 'Tonsillitis',
                'physical_examination' => 'Enlarged tonsils with erythema',
            ],
            'symptoms' => 'Fever and throat pain',
            'notes' => 'Hydration advised',
            'recommendations' => 'Review in 3 days',
        ]);

        $this->assertSame('Severe sore throat', $checkup->chief_complaint);
        $this->assertSame('Tonsillitis', $checkup->diagnosis);
        $this->assertSame('Enlarged tonsils with erythema', $checkup->examination);
    }

    public function test_visit_timeline_partial_renders_visit_badges_and_actions(): void
    {
        $patient = new Patient([
            'patient_id' => 'P-42',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
        ]);
        $patient->setAttribute('id', 42);

        $checkup = new PatientCheckup([
            'patient_id' => 42,
            'checkup_date' => now(),
            'symptoms' => 'Recurring headache',
            'notes' => 'Patient reports symptoms for 3 days.',
            'recommendations' => 'Migraine workup',
        ]);
        $checkup->setAttribute('id', 7);

        $checkup->setRelation('recorder', new User([
            'first_name' => 'Sara',
            'last_name' => 'Smith',
        ]));

        $checkup->setAttribute('is_most_recent_visit', true);
        $checkup->setAttribute('is_first_visit', false);
        $checkup->setAttribute('timeline_prescription_summary', 'Ibuprofen, Sumatriptan');
        $checkup->setAttribute('timeline_prescriptions', collect());
        $checkup->setAttribute('timeline_attachments', collect());

        $olderCheckup = new PatientCheckup([
            'patient_id' => 42,
            'checkup_date' => now()->subDays(10),
            'symptoms' => 'Sore throat',
            'notes' => 'Symptoms improving.',
            'recommendations' => 'Hydration and rest',
        ]);
        $olderCheckup->setAttribute('id', 8);
        $olderCheckup->setRelation('recorder', new User([
            'first_name' => 'Sara',
            'last_name' => 'Smith',
        ]));
        $olderCheckup->setAttribute('is_most_recent_visit', false);
        $olderCheckup->setAttribute('is_first_visit', true);
        $olderCheckup->setAttribute('timeline_prescription_summary', 'Amoxicillin');
        $olderCheckup->setAttribute('timeline_prescriptions', collect());
        $olderCheckup->setAttribute('timeline_attachments', collect());

        $visitTimeline = new LengthAwarePaginator(collect([$checkup, $olderCheckup]), 2, 8, 1);

        $html = view('patients.partials.visit-timeline-items', compact('patient', 'visitTimeline'))->render();

        $this->assertStringContainsString('Most Recent Visit', $html);
        $this->assertStringContainsString('First Visit', $html);
        $this->assertStringContainsString('View Details', $html);
        $this->assertStringContainsString('Ibuprofen, Sumatriptan', $html);
        $this->assertStringContainsString('Recurring headache', $html);
        $this->assertStringContainsString('data-visit-history-item="false"', $html);
        $this->assertStringContainsString('data-visit-history-item="true"', $html);
    }
}