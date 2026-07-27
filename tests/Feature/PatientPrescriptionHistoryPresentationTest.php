<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\SimplePrescription;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class PatientPrescriptionHistoryPresentationTest extends TestCase
{
    public function test_recent_prescriptions_partial_marks_older_prescriptions_as_collapsed_history(): void
    {
        $patient = new Patient([
            'patient_id' => 'P-42',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
        ]);
        $patient->setAttribute('id', 42);

        $doctor = new User([
            'first_name' => 'Test',
            'last_name' => 'Doctor',
        ]);

        $latestPrescription = new SimplePrescription([
            'prescription_number' => 'TLDEMO-5-05',
            'diagnosis' => 'Allergic contact dermatitis',
            'notes' => 'Topical anti-inflammatory plus antihistamine for itch control.',
            'prescribed_date' => Carbon::parse('2026-04-07'),
            'status' => 'active',
        ]);
        $latestPrescription->setAttribute('id', 5);
        $latestPrescription->setRelation('doctor', $doctor);
        $latestPrescription->created_at = Carbon::parse('2026-04-07 09:00:00');

        $olderPrescription = new Prescription([
            'prescription_number' => 'TLDEMO-5-04',
            'diagnosis' => 'Mild asthma exacerbation',
            'notes' => 'Short-acting bronchodilator with antihistamine support.',
            'prescribed_date' => Carbon::parse('2026-03-10'),
            'status' => 'completed',
        ]);
        $olderPrescription->setAttribute('id', 4);
        $olderPrescription->setRelation('doctor', $doctor);
        $olderPrescription->created_at = Carbon::parse('2026-03-10 09:00:00');

        $patient->setRelation('prescriptions', collect([$olderPrescription]));
        $patient->setRelation('simplePrescriptions', collect([$latestPrescription]));

        $html = view('patients.partials.recent-prescriptions', compact('patient'))->render();

        $this->assertStringContainsString('Show Full History', $html);
        $this->assertStringContainsString('Allergic contact dermatitis', $html);
        $this->assertStringContainsString('Mild asthma exacerbation', $html);
        $this->assertStringContainsString('data-prescription-history-item="false"', $html);
        $this->assertStringContainsString('data-prescription-history-item="true"', $html);
    }
}