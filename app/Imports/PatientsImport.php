<?php

namespace App\Imports;

use App\Models\Patient;
use App\Models\PatientCheckup;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PatientsImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $warnings = [];

    public function collection(Collection $rows)
    {
        $user = Auth::user();
        $rowNumber = 1; // Start from 1 since we have headers

        foreach ($rows as $row) {
            $rowNumber++;

            try {
                $data = $row->toArray();

                // Skip legacy template instruction rows from older downloads.
                if ($this->isTemplateInstructionRow($data)) {
                    continue;
                }

                $data['first_name'] = $this->normalizeText($data['first_name'] ?? '');
                $data['last_name'] = $this->normalizeText($data['last_name'] ?? '');

                // Skip empty rows
                if (empty($data['first_name']) && empty($data['last_name'])) {
                    continue;
                }

                // Normalize data to bypass empty cells and odd types
                $data['phone'] = $this->normalizeText($data['phone'] ?? null);
                $data['whatsapp_phone'] = $this->normalizeText($data['whatsapp_phone'] ?? null);
                $data['email'] = $this->normalizeText($data['email'] ?? null);
                $data['gender'] = $this->normalizeGender($data['gender'] ?? '');
                list($normalizedDob, $dobWarning) = $this->normalizeDate($data['date_of_birth'] ?? '');
                $data['date_of_birth'] = $normalizedDob; // may be null
                if ($dobWarning) { $this->warnings[] = "Row {$rowNumber}: {$dobWarning}"; }
                list($normalizedPreviousVisitDate, $previousVisitWarning) = $this->normalizeDate(
                    $data['previous_visit_date'] ?? '',
                    'historical visit will be ignored'
                );
                $data['previous_visit_date'] = $normalizedPreviousVisitDate;
                if ($previousVisitWarning) { $this->warnings[] = "Row {$rowNumber}: {$previousVisitWarning}"; }

                // Validate required fields
                $validator = Validator::make($data, [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'date_of_birth' => 'nullable|date',
                    'previous_visit_date' => 'nullable|date',
                    'gender' => 'nullable|in:male,female,other',
                    'phone' => 'nullable|max:20',
                    'email' => 'nullable|email|max:255',
                ]);
                $validator->setAttributeNames([
                    'first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'date_of_birth' => 'Date of Birth',
                    'previous_visit_date' => 'Previous Visit Date',
                    'gender' => 'Gender',
                    'phone' => 'Phone',
                    'email' => 'Email',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = "Row {$rowNumber}: " . implode('; ', $validator->errors()->all());
                    $this->skippedCount++;
                    continue;
                }

                // Generate unique patient ID
                $patientId = $this->generateUniquePatientId($user->clinic_id);

                // Check for duplicate patient (same name and phone in the same clinic)
                $exists = Patient::where('clinic_id', $user->clinic_id)
                    ->where('first_name', trim($data['first_name']))
                    ->where('last_name', trim($data['last_name']))
                    ->when(!empty(trim($data['phone'] ?? '')), function ($q) use ($data) {
                        return $q->where('phone', trim($data['phone']));
                    })
                    ->exists();

                if ($exists) {
                    $this->errors[] = "Row {$rowNumber}: Patient '" . ($data['first_name'] ?? '') . " " . ($data['last_name'] ?? '') . "' already exists";
                    $this->skippedCount++;
                    continue;
                }

                $finalDob = $data['date_of_birth'] ?: null;
                $finalGender = $data['gender'] !== '' ? $data['gender'] : null;

                // Parse numeric fields
                $height = $this->parseNumeric($data['height'] ?? '');
                $weight = $this->parseNumeric($data['weight'] ?? '');
                $bmi = null;

                // Calculate BMI if height and weight are provided
                if ($height && $weight && $height > 0) {
                    $heightInMeters = $height / 100; // Convert cm to meters
                    $bmi = round($weight / ($heightInMeters * $heightInMeters), 2);
                }

                try {
                    DB::transaction(function () use ($data, $patientId, $finalDob, $finalGender, $height, $weight, $bmi, $user) {
                        $patient = Patient::create([
                            'patient_id' => $patientId,
                            'first_name' => $data['first_name'],
                            'last_name' => $data['last_name'],
                            'date_of_birth' => $finalDob,
                            'gender' => $finalGender,
                            'phone' => $data['phone'],
                            'whatsapp_phone' => $data['whatsapp_phone'],
                            'email' => $data['email'],
                            'address' => $this->normalizeText($data['address'] ?? null),
                            'job' => $this->normalizeText($data['job'] ?? null),
                            'education' => $this->normalizeText($data['education'] ?? null),
                            'height' => $height,
                            'weight' => $weight,
                            'bmi' => $bmi,
                            'allergies' => $this->normalizeText($data['allergies'] ?? null),
                            'is_pregnant' => $this->parseBoolean($data['is_pregnant'] ?? ''),
                            'chronic_illnesses' => $this->normalizeText($data['chronic_illnesses'] ?? null),
                            'surgeries_history' => $this->normalizeText($data['surgeries_history'] ?? null),
                            'diet_history' => $this->normalizeText($data['diet_history'] ?? null),
                            'notes' => $this->normalizeText($data['notes'] ?? null),
                            'emergency_contact_name' => $this->normalizeText($data['emergency_contact_name'] ?? null),
                            'emergency_contact_phone' => $this->normalizeText($data['emergency_contact_phone'] ?? null),
                            'clinic_id' => $user->clinic_id,
                            'created_by' => $user->id,
                            'is_active' => $this->parseBoolean($data['is_active'] ?? 'true'),
                        ]);

                        if (!empty($data['previous_visit_date'])) {
                            $this->createHistoricalVisitEntry($patient, $data['previous_visit_date'], $user->id);
                        }
                    });

                    $this->importedCount++;
                } catch (\Illuminate\Database\QueryException $e) {
                    $this->errors[] = "Row {$rowNumber}: Database error while saving the patient. Please make sure the latest patient schema migration has been applied.";
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
        $value = $this->normalizeText($value);

        if ($value === null) {
            return null;
        }

        $cleaned = preg_replace('/[^\d.]/', '', $value);
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    /**
     * Parse boolean values
     */
    private function parseBoolean($value)
    {
        $value = $this->normalizeText($value);

        if ($value === null) {
            return false;
        }

        $value = strtolower($value);
        return in_array($value, ['true', '1', 'yes', 'y', 'on']);
    }

    /**
     * Normalize gender values to male/female/other; empty -> '' (handled later)
     */
    private function normalizeGender($value): string
    {
        $v = strtolower($this->normalizeText($value) ?? '');
        if ($v === '') return '';
        $map = [
            'm' => 'male', 'male' => 'male', '1' => 'male',
            'f' => 'female', 'female' => 'female', '0' => 'female',
            'other' => 'other', 'o' => 'other'
        ];
        return $map[$v] ?? ($v === 'male' || $v === 'female' || $v === 'other' ? $v : 'other');
    }

    /**
     * Normalize Excel dates: accepts Y-m-d strings or Excel serial numbers.
     * Returns array [Y-m-d|null, warning|null]
     */
    private function normalizeDate($value, string $invalidDisposition = 'will be left empty'): array
    {
        if ($value === null) {
            return [null, null];
        }

        if ($value instanceof DateTimeInterface) {
            return [Carbon::instance($value)->format('Y-m-d'), null];
        }

        if (is_numeric($value)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject($value);
                return [$dt->format('Y-m-d'), null];
            } catch (\Throwable $e) {
                return [null, "Invalid date detected; could not convert Excel serial ({$invalidDisposition})"];
            }
        }

        $v = $this->normalizeText($value);
        if ($v === null) {
            return [null, null];
        }

        foreach (['Y-m-d', 'Y/m/d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y', 'd.m.Y'] as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $v);
                if ($dt !== false) {
                    return [$dt->format('Y-m-d'), null];
                }
            } catch (\Throwable $e) {
                // Try next known format.
            }
        }

        try {
            return [Carbon::parse($v)->format('Y-m-d'), null];
        } catch (\Throwable $e) {
            return [null, "Invalid date format '{$v}' ({$invalidDisposition})"];
        }
    }

    private function normalizeText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function isTemplateInstructionRow(array $data): bool
    {
        $expectedHeaders = self::getExpectedHeaders();
        $nonEmptyValues = 0;
        $matchingLabels = 0;

        foreach ($expectedHeaders as $key => $label) {
            $value = $this->normalizeText($data[$key] ?? null);

            if ($value === null) {
                continue;
            }

            $nonEmptyValues++;

            if (strcasecmp($value, $label) === 0) {
                $matchingLabels++;
            }
        }

        return $nonEmptyValues > 0 && $nonEmptyValues === $matchingLabels;
    }

    /**
     * Create a historical visit entry so imported patients appear in the visit timeline.
     */
    private function createHistoricalVisitEntry(Patient $patient, string $previousVisitDate, int $recordedBy): void
    {
        PatientCheckup::create([
            'patient_id' => $patient->id,
            'recorded_by' => $recordedBy,
            'checkup_date' => Carbon::parse($previousVisitDate)->startOfDay(),
        ]);
    }


    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
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
            'date_of_birth' => 'Date of Birth (YYYY-MM-DD, optional)',
            'previous_visit_date' => 'Previous Visit Date (YYYY-MM-DD, optional)',
            'gender' => 'Gender (male/female/other, optional)',
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
                'previous_visit_date' => '2026-03-01',
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
                'previous_visit_date' => '',
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
