<?php

namespace Database\Seeders;

use App\Models\PediatricDrug;
use App\Models\PediatricDrugForm;
use App\Models\PediatricDosageRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PediatricDrugSeeder extends Seeder
{
    /**
     * Seed the pediatric drugs, forms, and dosage rules from exported JSON.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/pediatric_drugs_data.json');

        if (!file_exists($jsonPath)) {
            $this->command->error('pediatric_drugs_data.json not found in database/seeders/');
            return;
        }

        $drugs = json_decode(file_get_contents($jsonPath), true);

        if (empty($drugs)) {
            $this->command->warn('No drug data found in JSON file.');
            return;
        }

        $count = count($drugs);
        $this->command->info("Seeding {$count} pediatric drugs...");

        DB::beginTransaction();

        try {
            $drugCount = 0;
            $formCount = 0;
            $ruleCount = 0;

            foreach ($drugs as $drugData) {
                // Skip if drug already exists by generic_name
                $existing = PediatricDrug::where('generic_name', $drugData['generic_name'])->first();
                if ($existing) {
                    continue;
                }

                $drug = PediatricDrug::create([
                    'generic_name' => $drugData['generic_name'],
                    'brand_name' => $drugData['brand_name'] ?? null,
                    'category' => $drugData['category'] ?? null,
                    'description' => $drugData['description'] ?? null,
                    'is_active' => $drugData['is_active'] ?? true,
                    'is_system' => true,
                    'clinic_id' => null,
                ]);
                $drugCount++;

                // Seed forms
                if (!empty($drugData['forms'])) {
                    foreach ($drugData['forms'] as $formData) {
                        PediatricDrugForm::create([
                            'drug_id' => $drug->id,
                            'form' => $formData['form'],
                            'concentration' => $formData['concentration'] ?? null,
                            'concentration_mg' => $formData['concentration_mg'] ?? null,
                            'concentration_per_ml' => $formData['concentration_per_ml'] ?? null,
                        ]);
                        $formCount++;
                    }
                }

                // Seed dosage rules
                if (!empty($drugData['dosage_rules'])) {
                    foreach ($drugData['dosage_rules'] as $ruleData) {
                        PediatricDosageRule::create([
                            'drug_id' => $drug->id,
                            'mg_per_kg_min' => $ruleData['mg_per_kg_min'],
                            'mg_per_kg_max' => $ruleData['mg_per_kg_max'],
                            'max_daily_mg' => $ruleData['max_daily_mg'] ?? null,
                            'frequency_per_day' => $ruleData['frequency_per_day'] ?? null,
                            'frequency_hours' => $ruleData['frequency_hours'] ?? null,
                            'min_age_months' => $ruleData['min_age_months'] ?? null,
                            'max_age_months' => $ruleData['max_age_months'] ?? null,
                            'notes' => $ruleData['notes'] ?? null,
                        ]);
                        $ruleCount++;
                    }
                }
            }

            DB::commit();

            $this->command->info("✅ Done: {$drugCount} drugs, {$formCount} forms, {$ruleCount} dosage rules seeded.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Seeding failed: " . $e->getMessage());
        }
    }
}

