<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Clinic;

class TestPatientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first clinic (assuming there's at least one clinic)
        $clinic = Clinic::first();

        if (!$clinic) {
            $this->command->error('No clinic found. Please create a clinic first.');
            return;
        }

        // Get the first user to use as created_by
        $user = \App\Models\User::first();

        if (!$user) {
            $this->command->error('No user found. Please create a user first.');
            return;
        }

        $patients = [
            [
                'patient_id' => 'P001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
                'phone' => '+1234567890',
                'email' => 'john.doe@example.com',
                'address' => '123 Main Street, City, State 12345',
                'emergency_contact_name' => 'Jane Doe',
                'emergency_contact_phone' => '+1234567891',
                'allergies' => 'Penicillin',
                'chronic_illnesses' => 'Hypertension, Diabetes Type 2',
                'clinic_id' => $clinic->id,
                'created_by' => $user->id,
            ],
            [
                'patient_id' => 'P002',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'date_of_birth' => '1992-03-22',
                'gender' => 'female',
                'phone' => '+1234567892',
                'email' => 'sarah.johnson@example.com',
                'address' => '456 Oak Avenue, City, State 12345',
                'emergency_contact_name' => 'Mike Johnson',
                'emergency_contact_phone' => '+1234567893',
                'allergies' => null,
                'chronic_illnesses' => 'Asthma',
                'clinic_id' => $clinic->id,
                'created_by' => $user->id,
            ],
            [
                'patient_id' => 'P003',
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'date_of_birth' => '1978-11-08',
                'gender' => 'male',
                'phone' => '+1234567894',
                'email' => 'michael.brown@example.com',
                'address' => '789 Pine Street, City, State 12345',
                'emergency_contact_name' => 'Lisa Brown',
                'emergency_contact_phone' => '+1234567895',
                'allergies' => 'Shellfish, Nuts',
                'chronic_illnesses' => 'High cholesterol',
                'surgeries_history' => 'Previous heart surgery',
                'clinic_id' => $clinic->id,
                'created_by' => $user->id,
            ],
            [
                'patient_id' => 'P004',
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'date_of_birth' => '1995-09-12',
                'gender' => 'female',
                'phone' => '+1234567896',
                'email' => 'emily.davis@example.com',
                'address' => '321 Elm Street, City, State 12345',
                'emergency_contact_name' => 'Robert Davis',
                'emergency_contact_phone' => '+1234567897',
                'allergies' => null,
                'chronic_illnesses' => null,
                'clinic_id' => $clinic->id,
                'created_by' => $user->id,
            ],
            [
                'patient_id' => 'P005',
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'date_of_birth' => '1965-12-03',
                'gender' => 'male',
                'phone' => '+1234567898',
                'email' => 'david.wilson@example.com',
                'address' => '654 Maple Drive, City, State 12345',
                'emergency_contact_name' => 'Mary Wilson',
                'emergency_contact_phone' => '+1234567899',
                'allergies' => 'Latex',
                'chronic_illnesses' => 'Arthritis',
                'surgeries_history' => 'Previous knee replacement',
                'clinic_id' => $clinic->id,
                'created_by' => $user->id,
            ],
        ];

        foreach ($patients as $patientData) {
            // Check if patient already exists
            $existingPatient = Patient::where('patient_id', $patientData['patient_id'])
                                    ->where('clinic_id', $clinic->id)
                                    ->first();
            
            if (!$existingPatient) {
                Patient::create($patientData);
                $this->command->info("Created patient: {$patientData['first_name']} {$patientData['last_name']} ({$patientData['patient_id']})");
            } else {
                $this->command->info("Patient {$patientData['patient_id']} already exists, skipping...");
            }
        }

        $this->command->info('Test patients seeding completed!');
    }
}
