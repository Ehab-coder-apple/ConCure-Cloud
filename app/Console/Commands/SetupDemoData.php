<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Patient;
use App\Models\Clinic;

class SetupDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:demo-data {--clinic-id= : Specific clinic ID to setup data for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup demo patients and data for testing prescriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up demo data for prescription testing...');

        $clinicId = $this->option('clinic-id');

        if ($clinicId) {
            $clinic = Clinic::find($clinicId);
            if (!$clinic) {
                $this->error("Clinic with ID {$clinicId} not found.");
                return 1;
            }
            $clinics = collect([$clinic]);
        } else {
            $clinics = Clinic::all();
        }

        foreach ($clinics as $clinic) {
            $this->info("Setting up data for clinic: {$clinic->name} (ID: {$clinic->id})");

            // Get a doctor for this clinic
            $doctor = User::where('clinic_id', $clinic->id)
                         ->where('role', 'doctor')
                         ->first();

            if (!$doctor) {
                $this->warn("No doctor found for clinic {$clinic->name}. Skipping...");
                continue;
            }

            // Check if patients already exist
            $existingPatients = Patient::where('clinic_id', $clinic->id)->count();

            if ($existingPatients >= 3) {
                $this->info("Clinic {$clinic->name} already has {$existingPatients} patients. Skipping...");
                continue;
            }

            // Create demo patients
            $patients = [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'date_of_birth' => '1985-05-15',
                    'gender' => 'male',
                    'phone' => '123-456-7890',
                    'email' => 'john.doe@example.com',
                ],
                [
                    'first_name' => 'Sarah',
                    'last_name' => 'Johnson',
                    'date_of_birth' => '1990-08-22',
                    'gender' => 'female',
                    'phone' => '987-654-3210',
                    'email' => 'sarah.johnson@example.com',
                ],
                [
                    'first_name' => 'Ahmed',
                    'last_name' => 'Hassan',
                    'date_of_birth' => '1978-12-10',
                    'gender' => 'male',
                    'phone' => '555-123-4567',
                    'email' => 'ahmed.hassan@example.com',
                ],
            ];

            foreach ($patients as $patientData) {
                $patient = Patient::create([
                    'patient_id' => 'P' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'first_name' => $patientData['first_name'],
                    'last_name' => $patientData['last_name'],
                    'date_of_birth' => $patientData['date_of_birth'],
                    'gender' => $patientData['gender'],
                    'phone' => $patientData['phone'],
                    'email' => $patientData['email'],
                    'address' => '123 Demo Street, Demo City',
                    'clinic_id' => $clinic->id,
                    'created_by' => $doctor->id,
                    'is_active' => true,
                ]);

                $this->info("Created patient: {$patient->first_name} {$patient->last_name} ({$patient->patient_id})");
            }
        }

        $this->info('Demo data setup completed!');
        return 0;
    }
}
