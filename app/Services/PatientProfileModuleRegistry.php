<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;

class PatientProfileModuleRegistry
{
    public static function definitions(): array
    {
        return [
            'dental' => [
                'label' => __('Dental'),
                'icon' => 'fas fa-tooth',
                'description' => __('Dental summary, charts, treatments, and imaging linked to the patient.'),
            ],
            'pediatric' => [
                'label' => __('Pediatric'),
                'icon' => 'fas fa-baby',
                'description' => __('Child-specific birth, growth, and vaccination summary for eligible patients under 16.'),
                'max_age' => 15,
            ],
            'nutrition' => [
                'label' => __('Nutrition'),
                'icon' => 'fas fa-apple-whole',
                'description' => __('Diet plans, progress tracking, and nutrition-focused follow-up.'),
            ],
            'ent' => [
                'label' => __('ENT'),
                'icon' => 'fas fa-notes-medical',
                'description' => __('ENT findings, notes, visit links, and audiometry or scan uploads for the patient.'),
            ],
        ];
    }

    public static function all(): array
    {
        return collect(static::definitions())
            ->map(fn (array $definition, string $key) => ['key' => $key] + $definition)
            ->values()
            ->all();
    }

    public static function find(string $module): ?array
    {
        $definition = static::definitions()[$module] ?? null;

        return $definition ? ['key' => $module] + $definition : null;
    }

    public static function isKnown(string $module): bool
    {
        return static::find($module) !== null;
    }

    public static function isEnabledForClinic(?Clinic $clinic, string $module): bool
    {
        return $clinic?->hasModule($module) ?? true;
    }

    public static function modulesForClinic(?Clinic $clinic): array
    {
        return collect(static::all())
            ->filter(fn (array $module) => static::isEnabledForClinic($clinic, $module['key']))
            ->values()
            ->all();
    }

    public static function isVisibleToUser(?User $user, string $module): bool
    {
        if ($module !== 'ent') {
            return true;
        }

        return $user?->canAccessSection('ent') ?? true;
    }

    public static function filterVisibleToUser(iterable $modules, ?User $user)
    {
        return collect($modules)
            ->filter(fn (array $module) => static::isVisibleToUser($user, $module['key'] ?? ''))
            ->values();
    }

    public static function isEligibleForPatient(Patient $patient, string $module): bool
    {
        $definition = static::find($module);

        if (!$definition) {
            return false;
        }

        if (isset($definition['max_age']) && $patient->age >= ((int) $definition['max_age'] + 1)) {
            return false;
        }

        return true;
    }

    public static function isAvailableForPatient(Patient $patient, string $module): bool
    {
        if (!static::isEligibleForPatient($patient, $module)) {
            return false;
        }

        $clinic = $patient->relationLoaded('clinic') ? $patient->clinic : $patient->clinic;

        return static::isEnabledForClinic($clinic, $module);
    }

    public static function eligibleModulesForPatient(Patient $patient): array
    {
        return collect(static::all())
            ->filter(fn (array $module) => static::isAvailableForPatient($patient, $module['key']))
            ->values()
            ->all();
    }

    public static function defaultActiveModulesForPatient(Patient $patient): array
    {
        return collect(['dental', 'nutrition', 'ent', 'pediatric'])
            ->filter(fn (string $module) => static::isAvailableForPatient($patient, $module))
            ->values()
            ->all();
    }

    public static function summaryStats(Patient $patient, string $module): array
    {
        return match ($module) {
            'dental' => [
                ['label' => __('Charts'), 'value' => data_get($patient, 'dental_charts_count') ?? $patient->dentalCharts()->count()],
                ['label' => __('Treatments'), 'value' => data_get($patient, 'dental_treatments_count') ?? $patient->dentalTreatments()->count()],
                ['label' => __('Last Visit'), 'value' => data_get($patient, 'dental_last_visit_label', __('Not recorded'))],
            ],
            'pediatric' => [
                ['label' => __('Growth Entries'), 'value' => data_get($patient, 'growth_measurements_count') ?? $patient->growthMeasurements()->count()],
                ['label' => __('Growth Status'), 'value' => data_get($patient, 'pediatric_growth_status_label', __('Not recorded'))],
                ['label' => __('Vaccines'), 'value' => data_get($patient, 'vaccinations_count') ?? $patient->vaccinations()->count()],
            ],
            'nutrition' => [
                ['label' => __('Diet Plans'), 'value' => data_get($patient, 'diet_plans_count') ?? $patient->dietPlans()->count()],
                ['label' => __('Progress Records'), 'value' => data_get($patient, 'nutrition_progress_measurements_count') ?? $patient->nutritionProgressMeasurements()->count()],
                ['label' => __('Last Visit'), 'value' => data_get($patient, 'nutrition_last_visit_label') ?? (optional($patient->visits()->latest('visit_date')->first()?->visit_date)->format('M d, Y') ?: __('Not recorded'))],
            ],
            'ent' => [
                ['label' => __('Issues Logged'), 'value' => data_get($patient, 'ent_issue_count', 0)],
                ['label' => __('Dizziness'), 'value' => data_get($patient, 'ent_dizziness_label', __('No'))],
                ['label' => __('ENT Files'), 'value' => data_get($patient, 'ent_file_count', 0)],
            ],
            default => [
                ['label' => __('Status'), 'value' => __('Scaffolded')],
                ['label' => __('Records'), 'value' => 0],
            ],
        };
    }

    public static function moduleLinks(Patient $patient, string $module): array
    {
        return match ($module) {
            'dental' => [
                ['label' => __('Open Full Dental Module'), 'url' => route('patients.dental.show', ['patient' => $patient->id])],
                ['label' => __('Dental Charts'), 'url' => route('dental.charts.index', ['patient' => $patient->id])],
                ['label' => __('Procedure History'), 'url' => route('dental.treatments.index', ['patient_id' => $patient->id])],
                ['label' => __('Dental History'), 'url' => route('dental.history', ['patient' => $patient->id])],
            ],
            'pediatric' => static::isEligibleForPatient($patient, 'pediatric')
                ? [
                    ['label' => __('Open Full Pediatric Module'), 'url' => route('patients.pediatric.show', ['patient' => $patient->id])],
                    ['label' => __('Growth Chart'), 'url' => route('pediatric.growth-chart', ['patient' => $patient->id])],
                    ['label' => __('Vaccination Timeline'), 'url' => route('vaccination.show', ['patient' => $patient->id])],
                ]
                : [],
            'nutrition' => [
                ['label' => __('Open Full Nutrition Module'), 'url' => route('patients.nutrition.show', ['patient' => $patient->id])],
                ['label' => __('Nutrition Plans'), 'url' => route('nutrition.index', ['patient_id' => $patient->id])],
                ['label' => __('Progress Dashboard'), 'url' => route('nutrition.progress.dashboard', ['patient_id' => $patient->id])],
            ],
            'ent' => [
                ['label' => __('Open Full ENT Module'), 'url' => route('patients.ent.show', ['patient' => $patient->id])],
            ],
            default => [],
        };
    }

    public static function primaryAction(Patient $patient, string $module): ?array
    {
        $moduleDefinition = static::find($module);
        if (!$moduleDefinition) {
            return null;
        }

        $primaryLink = collect(static::moduleLinks($patient, $module))->first();

        return [
            'label' => $primaryLink['label'] ?? __('Open Full :module Module', ['module' => $moduleDefinition['label']]),
            'url' => $primaryLink['url'] ?? route('patients.modules.show', ['patient' => $patient->id, 'module' => $module]),
            'icon' => $moduleDefinition['icon'],
        ];
    }
}