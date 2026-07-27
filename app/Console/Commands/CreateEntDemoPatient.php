<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Models\EntRecord;
use App\Models\AudiometryTest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateEntDemoPatient extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'demo:ent-patient {--clinic_id=1}';

    /**
     * The console command description.
     */
    protected $description = 'Create a demo patient with ENT records and audiometry/audiogram data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->option('clinic_id');

        // Get a doctor from the clinic
        $doctor = User::where('clinic_id', $clinicId)
            ->whereIn('role', ['doctor', 'admin'])
            ->first();

        if (!$doctor) {
            $this->error("No doctor found for clinic ID {$clinicId}");
            return 1;
        }

        DB::beginTransaction();
        try {
            $this->info('Creating ENT demo patient...');

            // Create demo patient
            $patient = Patient::create([
                'clinic_id' => $clinicId,
                'patient_id' => 'ENT-DEMO-' . rand(1000, 9999),
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'date_of_birth' => '1978-05-15',
                'gender' => 'female',
                'phone_number' => '+1-555-0123',
                'email' => 'sarah.johnson.demo@example.com',
                'address' => '123 Medical Plaza, Suite 100',
                'city' => 'Springfield',
                'state' => 'IL',
                'postal_code' => '62701',
                'country' => 'USA',
                'blood_type' => 'A+',
                'allergies' => 'Penicillin',
                'medical_history' => 'History of recurrent ear infections, mild hypertension',
                'created_by' => $doctor->id,
            ]);

            $this->info("Created patient: {$patient->full_name} ({$patient->patient_id})");

            // Create ENT Record
            $entRecord = EntRecord::create([
                'patient_id' => $patient->id,
                'clinic_id' => $clinicId,
                'doctor_id' => $doctor->id,
                'visit_date' => now()->subDays(7),
                'chief_complaint' => 'Patient reports progressive hearing loss in both ears over the past 2 years, more pronounced in noisy environments. Also complains of occasional tinnitus in the right ear.',
                'ear_examination' => "Right Ear: Tympanic membrane intact, slightly retracted. No effusion. External auditory canal clear.\nLeft Ear: Tympanic membrane intact and mobile. No signs of infection or perforation. Canal clear of cerumen.",
                'nose_examination' => 'Nasal mucosa pink and moist. Septum midline. Inferior turbinates slightly enlarged bilaterally. No polyps or masses visualized.',
                'throat_examination' => 'Oropharynx: Mucosa pink, moist. Tonsils 1+ bilaterally, no exudate. Posterior pharyngeal wall normal. No masses.',
                'neck_examination' => 'No cervical lymphadenopathy. Thyroid normal size, no nodules palpable. Full range of motion.',
                'cranial_nerves' => 'CN II-XII grossly intact. Facial symmetry preserved. No nystagmus.',
                'diagnosis' => 'Bilateral sensorineural hearing loss, moderate severity. Possible noise-induced hearing loss (patient works in manufacturing). Tinnitus, right ear.',
                'icd10_code' => 'H90.3',
                'treatment_plan' => '1. Audiometric evaluation completed (see audiogram)\n2. Recommend hearing aid evaluation and fitting\n3. Counseling on hearing protection in workplace\n4. Consider ENT referral if no improvement',
                'medications' => 'No ototoxic medications currently. Continue current medications for hypertension.',
                'followup_date' => now()->addMonths(3),
                'notes' => 'Patient very cooperative during examination. Expressed interest in hearing aids. Discussed realistic expectations and options.',
                'created_by' => $doctor->id,
            ]);

            $this->info("Created ENT record for visit date: {$entRecord->visit_date->format('Y-m-d')}");

            // Create Audiometry Test with realistic data
            // Simulating moderate sensorineural hearing loss
            $audiometryTest = AudiometryTest::create([
                'patient_id' => $patient->id,
                'clinic_id' => $clinicId,
                'ent_record_id' => $entRecord->id,
                'test_date' => now()->subDays(7),
                'test_type' => 'pure_tone',
                
                // Right ear - moderate high-frequency hearing loss (typical noise-induced pattern)
                'right_ear_data' => [
                    '250' => 20,   // Normal at low frequencies
                    '500' => 25,
                    '1000' => 30,
                    '2000' => 45,  // Starts dropping
                    '3000' => 55,  // Significant loss
                    '4000' => 65,  // Worst at 4000 Hz (noise-induced notch)
                    '6000' => 60,
                    '8000' => 55,
                ],
                
                // Left ear - slightly better but similar pattern
                'left_ear_data' => [
                    '250' => 15,
                    '500' => 20,
                    '1000' => 25,
                    '2000' => 40,
                    '3000' => 50,
                    '4000' => 60,
                    '6000' => 55,
                    '8000' => 50,
                ],
                
                // Speech audiometry
                'right_srt' => 35,  // Speech Reception Threshold
                'left_srt' => 30,
                'right_wrs' => 85,  // Word Recognition Score (%)
                'left_wrs' => 90,
                
                // Tympanometry
                'right_tympanometry' => 'Type A (Normal)',
                'left_tympanometry' => 'Type A (Normal)',
                
                // Clinical interpretation
                'right_interpretation' => 'sensorineural_loss',
                'left_interpretation' => 'sensorineural_loss',
                
                'notes' => 'Pure tone audiometry performed in sound-treated booth. Patient cooperative and reliable responses. Air conduction thresholds obtained at all frequencies bilaterally. Classic "noise notch" pattern seen at 4000 Hz bilaterally, consistent with occupational noise exposure history.',
                'recommendations' => "1. Bilateral hearing aid fitting recommended\n2. Custom hearing protection for workplace mandatory\n3. Annual audiometric monitoring\n4. Educate on communication strategies\n5. Consider assistive listening devices for TV/phone\n6. Follow-up in 3 months after hearing aid trial",
                
                'performed_by' => $doctor->id,
                'created_by' => $doctor->id,
            ]);

            $this->info("Created audiometry test with pure tone audiogram data");

            DB::commit();

            $this->info('');
            $this->info('✅ Demo ENT patient created successfully!');
            $this->info('');
            $this->info('Patient Details:');
            $this->info("  Name: {$patient->full_name}");
            $this->info("  ID: {$patient->patient_id}");
            $this->info("  Age: 46 years old");
            $this->info('');
            $this->info('ENT Record:');
            $this->info("  Diagnosis: Bilateral sensorineural hearing loss (moderate)");
            $this->info("  Pattern: Noise-induced hearing loss with 4000 Hz notch");
            $this->info('');
            $this->info('Audiometry Test:');
            $this->info("  Right ear: 20-65 dB HL (moderate loss)");
            $this->info("  Left ear: 15-60 dB HL (moderate loss)");
            $this->info("  Speech: SRT 35/30 dB, WRS 85/90%");
            $this->info('');
            $this->info('Next Steps:');
            $this->info("  1. Navigate to ENT → ENT Records");
            $this->info("  2. Find patient 'Sarah Johnson'");
            $this->info("  3. View the ENT record and audiometry test");
            $this->info("  4. Click 'View Audiogram' to see the chart visualization");
            $this->info('');
            $this->info('The audiogram will show the characteristic noise-induced');
            $this->info('hearing loss pattern with maximum loss at 4000 Hz.');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to create demo patient: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
