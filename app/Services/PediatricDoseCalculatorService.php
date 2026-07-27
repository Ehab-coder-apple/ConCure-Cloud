<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PediatricDrug;
use App\Models\PediatricDrugForm;
use App\Models\PediatricDosageRule;

class PediatricDoseCalculatorService
{
    /**
     * Calculate dose for a patient + drug + form combination.
     *
     * @return array{
     *   weight_kg: float,
     *   age_months: int,
     *   rule: ?PediatricDosageRule,
     *   dose_min_mg: float,
     *   dose_max_mg: float,
     *   recommended_dose_mg: float,
     *   dose_ml: ?float,
     *   frequency_per_day: int,
     *   daily_dose_min_mg: float,
     *   daily_dose_max_mg: float,
     *   max_daily_mg: ?float,
     *   safety: array
     * }
     */
    public function calculate(Patient $patient, PediatricDrug $drug, PediatricDrugForm $form, ?float $overrideWeightKg = null): array
    {
        $weightKg = $overrideWeightKg ?? $patient->latest_weight_kg;
        $ageMonths = $patient->age_months;

        if (!$weightKg || $weightKg <= 0) {
            return [
                'error' => 'No weight available for this patient. Please record a weight measurement first.',
                'weight_kg' => null,
                'age_months' => $ageMonths,
            ];
        }

        $rule = $drug->findDosageRule($ageMonths, $weightKg);

        if (!$rule) {
            return [
                'error' => 'No dosage rule found for this drug matching the patient\'s age/weight.',
                'weight_kg' => $weightKg,
                'age_months' => $ageMonths,
            ];
        }

        // Per-dose calculations
        $doseMinMg = round($rule->mg_per_kg_min * $weightKg, 2);
        $doseMaxMg = round($rule->mg_per_kg_max * $weightKg, 2);
        $recommendedDoseMg = round(($doseMinMg + $doseMaxMg) / 2, 2);

        // Daily calculations
        $frequencyPerDay = $rule->frequency_per_day ?: 3;
        $dailyDoseMinMg = round($doseMinMg * $frequencyPerDay, 2);
        $dailyDoseMaxMg = round($doseMaxMg * $frequencyPerDay, 2);

        // Cap by max daily dose if set
        if ($rule->max_daily_mg && $dailyDoseMaxMg > $rule->max_daily_mg) {
            $dailyDoseMaxMg = $rule->max_daily_mg;
            $doseMaxMg = round($rule->max_daily_mg / $frequencyPerDay, 2);
            $recommendedDoseMg = min($recommendedDoseMg, $doseMaxMg);
        }

        // Convert to ml if liquid
        $doseMl = $form->convertMgToMl($recommendedDoseMg);

        // mg/kg calculations
        $mgPerKg = round($recommendedDoseMg / $weightKg, 2);
        $dailyRecommendedMg = round($recommendedDoseMg * $frequencyPerDay, 2);
        $dailyMgPerKg = round($dailyRecommendedMg / $weightKg, 2);
        $maxDailyMgPerKg = $rule->max_daily_mg ? round($rule->max_daily_mg / $weightKg, 2) : null;

        // Safety validation
        $safety = $this->validateSafety($recommendedDoseMg, $doseMinMg, $doseMaxMg, $rule, $ageMonths, $weightKg, $frequencyPerDay);

        return [
            'weight_kg' => $weightKg,
            'age_months' => $ageMonths,
            'rule' => $rule,
            'dose_min_mg' => $doseMinMg,
            'dose_max_mg' => $doseMaxMg,
            'recommended_dose_mg' => $recommendedDoseMg,
            'dose_ml' => $doseMl,
            'dose_min_ml' => $form->convertMgToMl($doseMinMg),
            'dose_max_ml' => $form->convertMgToMl($doseMaxMg),
            'mg_per_kg' => $mgPerKg,
            'mg_per_kg_min' => $rule->mg_per_kg_min,
            'mg_per_kg_max' => $rule->mg_per_kg_max,
            'frequency_per_day' => $frequencyPerDay,
            'frequency_hours' => $rule->frequency_hours,
            'daily_dose_mg' => $dailyRecommendedMg,
            'daily_mg_per_kg' => $dailyMgPerKg,
            'daily_dose_min_mg' => $dailyDoseMinMg,
            'daily_dose_max_mg' => $dailyDoseMaxMg,
            'max_daily_mg' => $rule->max_daily_mg,
            'max_daily_mg_per_kg' => $maxDailyMgPerKg,
            'safety' => $safety,
        ];
    }

    /**
     * Validate safety of a specific dose.
     */
    public function validateSafety(float $doseMg, float $minMg, float $maxMg, PediatricDosageRule $rule, int $ageMonths, float $weightKg, int $frequencyPerDay): array
    {
        $messages = [];
        $status = 'safe';

        $mgPerKg = round($doseMg / $weightKg, 2);
        $dailyDoseMg = round($doseMg * $frequencyPerDay, 2);
        $dailyMgPerKg = round($dailyDoseMg / $weightKg, 2);

        // Dose range check
        if ($doseMg > $maxMg) {
            $status = 'danger';
            $messages[] = "Dose ({$doseMg}mg / {$mgPerKg} mg/kg) exceeds maximum safe dose ({$maxMg}mg) for {$weightKg}kg patient.";
        } elseif ($doseMg < $minMg) {
            $status = 'warning';
            $messages[] = "Dose ({$doseMg}mg / {$mgPerKg} mg/kg) is below minimum effective dose ({$minMg}mg) for {$weightKg}kg patient.";
        }

        // Daily max check (absolute mg)
        if ($rule->max_daily_mg) {
            $maxDailyMgPerKg = round($rule->max_daily_mg / $weightKg, 2);
            if ($dailyDoseMg > $rule->max_daily_mg) {
                $status = 'danger';
                $messages[] = "Daily dose: {$dailyMgPerKg} mg/kg/day exceeds max {$maxDailyMgPerKg} mg/kg/day ({$dailyDoseMg}mg > {$rule->max_daily_mg}mg).";
            } elseif ($dailyDoseMg > $rule->max_daily_mg * 0.9 && $status !== 'danger') {
                $status = ($status === 'danger') ? 'danger' : 'warning';
                $pct = round(($dailyDoseMg / $rule->max_daily_mg) * 100);
                $messages[] = "Daily dose is {$pct}% of max limit ({$dailyMgPerKg} mg/kg/day, max: {$maxDailyMgPerKg} mg/kg/day).";
            }
        }

        // Age restriction check
        if ($rule->min_age_months !== null && $ageMonths < $rule->min_age_months) {
            $status = 'danger';
            $messages[] = "Patient age ({$ageMonths} months) is below minimum age ({$rule->min_age_months} months) for this drug.";
        }
        if ($rule->max_age_months !== null && $ageMonths > $rule->max_age_months) {
            $status = ($status === 'danger') ? 'danger' : 'warning';
            $messages[] = "Patient age ({$ageMonths} months) exceeds recommended max age ({$rule->max_age_months} months).";
        }

        if (empty($messages)) {
            $messages[] = "Dose is within the safe therapeutic range ({$mgPerKg} mg/kg).";
        }

        return [
            'status' => $status,
            'messages' => $messages,
            'message' => implode(' ', $messages),
            'recommended_range' => ['min' => $minMg, 'max' => $maxMg],
            'mg_per_kg' => $mgPerKg,
            'daily_mg_per_kg' => $dailyMgPerKg,
            'daily_dose_mg' => $dailyDoseMg,
        ];
    }

    /**
     * Validate a custom (overridden) dose.
     */
    public function validateCustomDose(float $customDoseMg, Patient $patient, PediatricDrug $drug, PediatricDrugForm $form, int $frequencyPerDay): array
    {
        $weightKg = $patient->latest_weight_kg;
        $ageMonths = $patient->age_months;
        $rule = $drug->findDosageRule($ageMonths, $weightKg);

        if (!$rule || !$weightKg) {
            return ['status' => 'warning', 'message' => 'Cannot validate: missing weight or dosage rule.', 'messages' => ['Cannot validate.']];
        }

        $minMg = round($rule->mg_per_kg_min * $weightKg, 2);
        $maxMg = round($rule->mg_per_kg_max * $weightKg, 2);

        return $this->validateSafety($customDoseMg, $minMg, $maxMg, $rule, $ageMonths, $weightKg, $frequencyPerDay);
    }
}

