<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientVaccination;
use App\Models\ScheduleItem;
use App\Models\VaccinationSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VaccinationService
{
    /**
     * Generate all vaccination records for a patient based on their clinic's schedule.
     */
    public function generateScheduleForPatient(Patient $patient): Collection
    {
        $schedule = $this->resolveSchedule($patient);

        if (!$schedule || !$patient->date_of_birth) {
            return collect();
        }

        // Assign the schedule to the patient (freeze it)
        if (!$patient->vaccination_schedule_id) {
            $patient->update(['vaccination_schedule_id' => $schedule->id]);
        }

        $dob = Carbon::parse($patient->date_of_birth);
        $items = $schedule->items()->with('vaccine')->get();
        $created = collect();

        foreach ($items as $item) {
            // Skip if record already exists
            $exists = PatientVaccination::where('patient_id', $patient->id)
                ->where('vaccine_id', $item->vaccine_id)
                ->where('dose_number', $item->dose_number)
                ->exists();

            if ($exists) {
                continue;
            }

            $scheduledDate = $item->calculateRecommendedDate($dob);

            $vaccination = PatientVaccination::create([
                'patient_id' => $patient->id,
                'vaccine_id' => $item->vaccine_id,
                'dose_number' => $item->dose_number,
                'scheduled_date' => $scheduledDate,
                'status' => $scheduledDate->isFuture() ? 'upcoming' : 'missed',
            ]);

            $created->push($vaccination);
        }

        return $created;
    }

    /**
     * Resolve the vaccination schedule for a patient.
     * Priority: Patient frozen schedule > Clinic override > Country default
     */
    public function resolveSchedule(Patient $patient): ?VaccinationSchedule
    {
        // 1. Patient already has a frozen schedule
        if ($patient->vaccination_schedule_id) {
            return $patient->vaccinationSchedule;
        }

        // 2. Clinic override
        $clinic = $patient->clinic;
        if (!$clinic) {
            return null;
        }

        return $clinic->effective_vaccination_schedule;
    }

    /**
     * Update statuses for all vaccinations of a patient.
     */
    public function updateStatuses(Patient $patient): void
    {
        if (!$patient->date_of_birth || !$patient->vaccination_schedule_id) {
            return;
        }

        $dob = Carbon::parse($patient->date_of_birth);
        $schedule = $patient->vaccinationSchedule;
        if (!$schedule) return;

        $items = $schedule->items()->get()->keyBy(function ($item) {
            return $item->vaccine_id . '-' . $item->dose_number;
        });

        $vaccinations = $patient->vaccinations()->get();

        foreach ($vaccinations as $vaccination) {
            $key = $vaccination->vaccine_id . '-' . $vaccination->dose_number;
            $item = $items->get($key);

            if (!$item) continue;

            // Already administered — calculate status
            if ($vaccination->given_date) {
                $vaccination->status = $this->calculateGivenStatus($vaccination, $item, $dob);
                $vaccination->delay_days = $vaccination->given_date->diffInDays($vaccination->scheduled_date, false);
                $vaccination->save();
                continue;
            }

            // Not yet given — check upcoming vs missed
            $maxDate = $item->calculateMaxDate($dob);
            $gracePeriodEnd = $vaccination->scheduled_date->copy()->addDays($item->grace_period_days);
            $today = Carbon::today();

            if ($today->lt($vaccination->scheduled_date)) {
                $vaccination->status = PatientVaccination::STATUS_UPCOMING;
            } elseif ($maxDate && $today->gt($maxDate)) {
                $vaccination->status = PatientVaccination::STATUS_MISSED;
            } elseif ($today->gt($gracePeriodEnd)) {
                $vaccination->status = PatientVaccination::STATUS_DELAYED;
            } else {
                $vaccination->status = PatientVaccination::STATUS_UPCOMING;
            }

            $vaccination->save();
        }
    }

    /**
     * Calculate status when a vaccine has been given.
     */
    protected function calculateGivenStatus(PatientVaccination $vacc, ScheduleItem $item, Carbon $dob): string
    {
        $givenDate = $vacc->given_date;
        $scheduledDate = $vacc->scheduled_date;
        $graceEnd = $scheduledDate->copy()->addDays($item->grace_period_days);

        if ($givenDate->lte($graceEnd)) {
            return PatientVaccination::STATUS_ON_TIME;
        }

        return PatientVaccination::STATUS_DELAYED;
    }

    /**
     * Record a given vaccination.
     */
    public function recordVaccination(PatientVaccination $vaccination, array $data): PatientVaccination
    {
        $vaccination->update([
            'given_date' => $data['given_date'],
            'batch_number' => $data['batch_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'administered_by' => $data['administered_by'] ?? null,
            'recorded_by' => $data['recorded_by'] ?? auth()->id(),
        ]);

        // Recalculate status
        $patient = $vaccination->patient;
        $this->updateStatuses($patient);

        return $vaccination->fresh();
    }

    /**
     * Get vaccination completion stats for a patient.
     */
    public function getCompletionStats(Patient $patient): array
    {
        $vaccinations = $patient->vaccinations;
        $total = $vaccinations->count();
        $given = $vaccinations->whereNotNull('given_date')->count();
        $missed = $vaccinations->where('status', PatientVaccination::STATUS_MISSED)->count();
        $upcoming = $vaccinations->where('status', PatientVaccination::STATUS_UPCOMING)->count();
        $delayed = $vaccinations->where('status', PatientVaccination::STATUS_DELAYED)->count();

        return [
            'total' => $total,
            'given' => $given,
            'missed' => $missed,
            'upcoming' => $upcoming,
            'delayed' => $delayed,
            'completion_percentage' => $total > 0 ? round(($given / $total) * 100, 1) : 0,
            'next_due' => $vaccinations
                ->where('status', PatientVaccination::STATUS_UPCOMING)
                ->sortBy('scheduled_date')
                ->first(),
        ];
    }

    /**
     * Import a vaccination schedule from JSON.
     */
    public function importScheduleFromJson(array $data): VaccinationSchedule
    {
        $country = \App\Models\Country::where('iso_code', $data['country'])->firstOrFail();

        $schedule = VaccinationSchedule::create([
            'country_id' => $country->id,
            'name' => $data['schedule'],
            'version' => $data['version'] ?? '1.0',
            'is_default' => $data['is_default'] ?? false,
            'effective_from' => $data['effective_from'] ?? now(),
            'is_active' => true,
        ]);

        $sortOrder = 0;
        foreach ($data['vaccines'] as $vaccineData) {
            $vaccine = \App\Models\Vaccine::firstOrCreate(
                ['code' => $vaccineData['code']],
                ['global_name' => $vaccineData['name'] ?? $vaccineData['code'], 'is_active' => true]
            );

            // Add translations if provided
            if (isset($vaccineData['translations'])) {
                foreach ($vaccineData['translations'] as $lang => $trans) {
                    \App\Models\VaccineTranslation::updateOrCreate(
                        ['vaccine_id' => $vaccine->id, 'language_code' => $lang],
                        ['name' => $trans['name'], 'description' => $trans['description'] ?? null]
                    );
                }
            }

            ScheduleItem::create([
                'schedule_id' => $schedule->id,
                'vaccine_id' => $vaccine->id,
                'dose_number' => $vaccineData['dose'] ?? 1,
                'recommended_age_value' => $vaccineData['age']['value'],
                'recommended_age_unit' => $vaccineData['age']['unit'] ?? 'months',
                'min_age_value' => $vaccineData['min_age'] ?? null,
                'max_age_value' => $vaccineData['max_age'] ?? null,
                'grace_period_days' => $vaccineData['grace'] ?? 7,
                'is_mandatory' => $vaccineData['mandatory'] ?? true,
                'sort_order' => $sortOrder++,
            ]);
        }

        return $schedule->load('items.vaccine');
    }

    /**
     * Batch update statuses for all patients (for cron job).
     */
    public function batchUpdateStatuses(?int $clinicId = null): int
    {
        $query = Patient::whereNotNull('vaccination_schedule_id')
            ->whereNotNull('date_of_birth');

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        $count = 0;
        $query->chunk(100, function ($patients) use (&$count) {
            foreach ($patients as $patient) {
                $this->updateStatuses($patient);
                $count++;
            }
        });

        return $count;
    }
}

