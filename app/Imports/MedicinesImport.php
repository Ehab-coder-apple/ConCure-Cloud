<?php

namespace App\Imports;

use App\Models\Medicine;
use App\Models\MedicineForm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MedicinesImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function collection(Collection $rows)
    {
        $user = Auth::user();
        
        if (!$user || !$user->clinic_id) {
            $this->errors[] = "User must be assigned to a clinic to import medicines.";
            return;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because of header row and 0-based index
            
            try {
                // Skip empty rows
                if (empty(trim($row['name'] ?? ''))) {
                    continue;
                }

                // Validate required fields (form is resolved/created below, so
                // only enforce its presence here, not membership in FORMS).
                $validator = Validator::make($row->toArray(), [
                    'name' => 'required|string|max:255',
                    'form' => 'required|string|max:80',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    $this->skippedCount++;
                    continue;
                }

                // Resolve the form: built-in slug, existing clinic-custom form,
                // or auto-create a new MedicineForm from the free-text label.
                $form = $this->resolveForm($row['form'], $user->clinic_id, $user->id);
                if ($form === null) {
                    $this->errors[] = "Row {$rowNumber}: Invalid form '{$row['form']}'.";
                    $this->skippedCount++;
                    continue;
                }

                // Check for duplicate medicine in the same clinic
                $exists = Medicine::where('clinic_id', $user->clinic_id)
                    ->where('name', trim($row['name']))
                    ->where('dosage', trim($row['dosage'] ?? ''))
                    ->where('form', $form)
                    ->exists();

                if ($exists) {
                    $this->errors[] = "Row {$rowNumber}: Medicine '{$row['name']}' with same dosage and form already exists";
                    $this->skippedCount++;
                    continue;
                }

                // Create the medicine
                try {
                    Medicine::create([
                        'name' => trim($row['name']),
                        'generic_name' => trim($row['generic_name'] ?? ''),
                        'brand_name' => trim($row['brand_name'] ?? ''),
                        'dosage' => trim($row['dosage'] ?? ''),
                        'form' => $form,
                        'description' => trim($row['description'] ?? ''),
                        'side_effects' => trim($row['side_effects'] ?? ''),
                        'contraindications' => trim($row['contraindications'] ?? ''),
                        'is_frequent' => $this->parseBoolean($row['is_frequent'] ?? ''),
                        'is_active' => $this->parseBoolean($row['is_active'] ?? 'true'),
                        'clinic_id' => $user->clinic_id,
                        'created_by' => $user->id,
                    ]);

                    $this->importedCount++;
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle database constraint violations
                    if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                        $this->errors[] = "Row {$rowNumber} with name '{$row['name']}': Duplicate entry (medicine already exists)";
                    } elseif (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                        $this->errors[] = "Row {$rowNumber} with name '{$row['name']}': Database constraint violation - " . $e->getMessage();
                    } else {
                        $this->errors[] = "Row {$rowNumber} with name '{$row['name']}': Database error - " . $e->getMessage();
                    }
                    $this->skippedCount++;
                    continue;
                }

            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber} with name '{$row['name']}': " . $e->getMessage();
                $this->skippedCount++;
            }
        }
    }

    /**
     * Resolve a form value from an imported row to a stored slug.
     *
     * Accepts (in priority order):
     *   1. A built-in slug   ("tablet", "syrup", ...)
     *   2. A built-in label  ("Tablet", "Syrup", ...) - matched case-insensitively
     *   3. An existing custom MedicineForm key or label for this clinic
     *   4. An unknown free-text label - auto-creates a clinic-scoped MedicineForm
     *      and returns its slug, so subsequent rows hit case (3).
     *
     * Returns null only if the input is empty after trimming.
     */
    private function resolveForm($raw, ?int $clinicId, ?int $userId): ?string
    {
        $value = trim((string) $raw);
        if ($value === '' || $clinicId === null) {
            return null;
        }

        $lower = strtolower($value);

        // 1. Built-in slug match
        if (array_key_exists($lower, Medicine::FORMS)) {
            return $lower;
        }

        // 2. Built-in label match (case-insensitive)
        foreach (Medicine::FORMS as $slug => $label) {
            if (strtolower($label) === $lower) {
                return $slug;
            }
        }

        // 3. Existing clinic-custom form (by key or label)
        $existing = MedicineForm::byClinic($clinicId)
            ->where(function ($q) use ($lower) {
                $q->whereRaw('LOWER(key) = ?', [$lower])
                  ->orWhereRaw('LOWER(label) = ?', [$lower]);
            })
            ->first();
        if ($existing) {
            return $existing->key;
        }

        // 4. Auto-create a new clinic-scoped form. Avoid shadowing a built-in
        // slug if the generated key happens to collide.
        $key = MedicineForm::makeKey($value);
        if ($key === '') {
            return null;
        }
        if (array_key_exists($key, Medicine::FORMS)) {
            return $key;
        }

        MedicineForm::firstOrCreate(
            ['clinic_id' => $clinicId, 'key' => $key],
            ['label' => $value, 'created_by' => $userId]
        );

        return $key;
    }

    /**
     * Parse boolean values from various string representations
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'y', 'on', 'active']);
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
            'name' => 'Medicine Name (Required)',
            'generic_name' => 'Generic Name',
            'brand_name' => 'Brand Name',
            'dosage' => 'Dosage (e.g., 500mg)',
            'form' => 'Form (Required: ' . implode(', ', array_keys(Medicine::FORMS)) . ' - or any custom label, which will be added to your clinic\'s form list)',
            'description' => 'Description',
            'side_effects' => 'Side Effects',
            'contraindications' => 'Contraindications',
            'is_frequent' => 'Is Frequent (true/false)',
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
                'name' => 'Amoxicillin',
                'generic_name' => 'Amoxicillin Trihydrate',
                'brand_name' => 'Amoxil',
                'dosage' => '500mg',
                'form' => 'tablet',
                'description' => 'Antibiotic used to treat bacterial infections',
                'side_effects' => 'Nausea, diarrhea, stomach upset',
                'contraindications' => 'Allergy to penicillin',
                'is_frequent' => 'true',
                'is_active' => 'true',
            ],
            [
                'name' => 'Paracetamol',
                'generic_name' => 'Acetaminophen',
                'brand_name' => 'Tylenol',
                'dosage' => '500mg',
                'form' => 'tablet',
                'description' => 'Pain reliever and fever reducer',
                'side_effects' => 'Rare: liver damage with overdose',
                'contraindications' => 'Severe liver disease',
                'is_frequent' => 'true',
                'is_active' => 'true',
            ],
            [
                'name' => 'Cough Syrup',
                'generic_name' => 'Dextromethorphan',
                'brand_name' => 'Robitussin',
                'dosage' => '15mg/5ml',
                'form' => 'syrup',
                'description' => 'Cough suppressant',
                'side_effects' => 'Drowsiness, dizziness',
                'contraindications' => 'MAO inhibitor use',
                'is_frequent' => 'false',
                'is_active' => 'true',
            ],
        ];
    }
}
