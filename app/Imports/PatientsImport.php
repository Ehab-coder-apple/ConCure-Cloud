<?php

namespace App\Imports;

use App\Models\Patient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PatientsImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function collection(Collection $rows)
    {
        $user = Auth::user();
        $rowNumber = 1; // Start from 1 since we have headers

        foreach ($rows as $row) {
            $rowNumber++;
            
            try {
                // Skip empty rows
                if (empty(trim($row['first_name'] ?? '')) && empty(trim($row['last_name'] ?? ''))) {
                    continue;
                }

                // Validate required fields
                $validator = Validator::make($row->toArray(), [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'date_of_birth' => 'nullable|date',
                    'gender' => 'nullable|in:male,female',
                    'phone' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:255',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    $this->skippedCount++;
                    continue;
                }

                // Generate unique patient ID
                $patientId = $this->generateUniquePatientId($user->clinic_id);

                // Check for duplicate patient (same name and phone in the same clinic)
                $exists = Patient::where('clinic_id', $user->clinic_id)
                    ->where('first_name', trim($row['first_name']))
                    ->where('last_name', trim($row['last_name']))
                    ->when(!empty(trim($row['phone'] ?? '')), function ($q) use ($row) {
                        return $q->where('phone', trim($row['phone']));
                    })
                    ->exists();

                if ($exists) {
                    $this->errors[] = "Row {$rowNumber}: Patient '{$row['first_name']} {$row['last_name']}' already exists";
                    $this->skippedCount++;
                    continue;
                }

                // Parse date of birth
                $dateOfBirth = null;
                if (!empty(trim($row['date_of_birth'] ?? ''))) {
                    try {
                        $dateOfBirth = \Carbon\Carbon::parse(trim($row['date_of_birth']))->format('Y-m-d');
                    } catch (\Exception $e) {
                        $this->errors[] = "Row {$rowNumber}: Invalid date format for date_of_birth";
                        $this->skippedCount++;
                        continue;
                    }
                }

                // Parse numeric fields
                $height = $this->parseNumeric($row['height'] ?? '');
                $weight = $this->parseNumeric($row['weight'] ?? '');
                $bmi = null;
                
                // Calculate BMI if height and weight are provided
                if ($height && $weight && $height > 0) {
                    $heightInMeters = $height / 100; // Convert cm to meters
                    $bmi = round($weight / ($heightInMeters * $heightInMeters), 2);
                }

                // Create the patient
                try {
                    Patient::create([
                        'patient_id' => $patientId,
                        'first_name' => trim($row['first_name']),
                        'last_name' => trim($row['last_name']),
                        'date_of_birth' => $dateOfBirth,
                        'gender' => strtolower(trim($row['gender'] ?? '')),
                        'phone' => trim($row['phone'] ?? ''),
                        'whatsapp_phone' => trim($row['whatsapp_phone'] ?? ''),
                        'email' => trim($row['email'] ?? ''),
                        'address' => trim($row['address'] ?? ''),
                        'job' => trim($row['job'] ?? ''),
                        'education' => trim($row['education'] ?? ''),
                        'height' => $height,
                        'weight' => $weight,
                        'bmi' => $bmi,
                        'allergies' => trim($row['allergies'] ?? ''),
                        'is_pregnant' => $this->parseBoolean($row['is_pregnant'] ?? ''),
                        'chronic_illnesses' => trim($row['chronic_illnesses'] ?? ''),
                        'surgeries_history' => trim($row['surgeries_history'] ?? ''),
                        'diet_history' => trim($row['diet_history'] ?? ''),
                        'notes' => trim($row['notes'] ?? ''),
                        'emergency_contact_name' => trim($row['emergency_contact_name'] ?? ''),
                        'emergency_contact_phone' => trim($row['emergency_contact_phone'] ?? ''),
                        'clinic_id' => $user->clinic_id,
                        'created_by' => $user->id,
                        'is_active' => $this->parseBoolean($row['is_active'] ?? 'true'),
                    ]);

                    $this->importedCount++;
                } catch (\Illuminate\Database\QueryException $e) {
                    $this->errors[] = "Row {$rowNumber}: Database error - " . $e->getMessage();
                    $this->skippedCount++;
                    continue;
                }

            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $this->skippedCount++;
            }
        }
    }

    /**
     * Generate a unique patient ID for the clinic
     */
    private function generateUniquePatientId($clinicId)
    {
        do {
            $patientId = 'P' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Patient::where('clinic_id', $clinicId)->where('patient_id', $patientId)->exists());

        return $patientId;
    }

    /**
     * Parse numeric values
     */
    private function parseNumeric($value)
    {
        if (empty(trim($value))) {
            return null;
        }
        
        $cleaned = preg_replace('/[^\d.]/', '', trim($value));
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    /**
     * Parse boolean values
     */
    private function parseBoolean($value)
    {
        if (empty(trim($value))) {
            return false;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'y', 'on']);
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get expected headers for the import template
     */
    public static function getExpectedHeaders(): array
    {
        return [
            'first_name' => 'First Name (Required)',
            'last_name' => 'Last Name (Required)',
            'date_of_birth' => 'Date of Birth (YYYY-MM-DD)',
            'gender' => 'Gender (male/female)',
            'phone' => 'Phone Number',
            'whatsapp_phone' => 'WhatsApp Phone',
            'email' => 'Email Address',
            'address' => 'Address',
            'job' => 'Job/Occupation',
            'education' => 'Education Level',
            'height' => 'Height (cm)',
            'weight' => 'Weight (kg)',
            'allergies' => 'Allergies',
            'is_pregnant' => 'Is Pregnant (true/false)',
            'chronic_illnesses' => 'Chronic Illnesses',
            'surgeries_history' => 'Surgeries History',
            'diet_history' => 'Diet History',
            'notes' => 'Notes',
            'emergency_contact_name' => 'Emergency Contact Name',
            'emergency_contact_phone' => 'Emergency Contact Phone',
            'is_active' => 'Is Active (true/false, default: true)',
        ];
    }

    /**
     * Get sample data for the template
     */
    public static function getSampleData(): array
    {
        return [
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Hassan',
                'date_of_birth' => '1985-03-15',
                'gender' => 'male',
                'phone' => '+9647501234567',
                'whatsapp_phone' => '+9647501234567',
                'email' => 'ahmed.hassan@email.com',
                'address' => 'Baghdad, Iraq',
                'job' => 'Engineer',
                'education' => 'Bachelor',
                'height' => '175',
                'weight' => '70',
                'allergies' => 'Penicillin',
                'is_pregnant' => 'false',
                'chronic_illnesses' => 'Diabetes Type 2',
                'surgeries_history' => 'Appendectomy 2010',
                'diet_history' => 'Low carb diet',
                'notes' => 'Regular checkups needed',
                'emergency_contact_name' => 'Fatima Hassan',
                'emergency_contact_phone' => '+9647509876543',
                'is_active' => 'true',
            ],
            [
                'first_name' => 'Fatima',
                'last_name' => 'Ali',
                'date_of_birth' => '1990-07-22',
                'gender' => 'female',
                'phone' => '+9647502345678',
                'whatsapp_phone' => '+9647502345678',
                'email' => 'fatima.ali@email.com',
                'address' => 'Erbil, Iraq',
                'job' => 'Teacher',
                'education' => 'Master',
                'height' => '160',
                'weight' => '55',
                'allergies' => '',
                'is_pregnant' => 'true',
                'chronic_illnesses' => '',
                'surgeries_history' => '',
                'diet_history' => 'Vegetarian',
                'notes' => 'Pregnant - 2nd trimester',
                'emergency_contact_name' => 'Omar Ali',
                'emergency_contact_phone' => '+9647508765432',
                'is_active' => 'true',
            ],
        ];
    }
}
