<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ScheduleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'vaccine_id',
        'dose_number',
        'recommended_age_value',
        'recommended_age_unit',
        'min_age_value',
        'max_age_value',
        'grace_period_days',
        'is_mandatory',
        'sort_order',
    ];

    protected $casts = [
        'dose_number' => 'integer',
        'recommended_age_value' => 'integer',
        'min_age_value' => 'integer',
        'max_age_value' => 'integer',
        'grace_period_days' => 'integer',
        'is_mandatory' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(VaccinationSchedule::class, 'schedule_id');
    }

    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(Vaccine::class);
    }

    /**
     * Calculate the recommended date based on DOB.
     */
    public function calculateRecommendedDate(Carbon $dob): Carbon
    {
        return match ($this->recommended_age_unit) {
            'days' => $dob->copy()->addDays($this->recommended_age_value),
            'weeks' => $dob->copy()->addWeeks($this->recommended_age_value),
            'months' => $dob->copy()->addMonths($this->recommended_age_value),
            'years' => $dob->copy()->addYears($this->recommended_age_value),
            default => $dob->copy()->addMonths($this->recommended_age_value),
        };
    }

    /**
     * Calculate the min allowed date based on DOB.
     */
    public function calculateMinDate(Carbon $dob): ?Carbon
    {
        if ($this->min_age_value === null) {
            return null;
        }
        return match ($this->recommended_age_unit) {
            'days' => $dob->copy()->addDays($this->min_age_value),
            'weeks' => $dob->copy()->addWeeks($this->min_age_value),
            'months' => $dob->copy()->addMonths($this->min_age_value),
            'years' => $dob->copy()->addYears($this->min_age_value),
            default => $dob->copy()->addMonths($this->min_age_value),
        };
    }

    /**
     * Calculate the max allowed date based on DOB.
     */
    public function calculateMaxDate(Carbon $dob): ?Carbon
    {
        if ($this->max_age_value === null) {
            return null;
        }
        return match ($this->recommended_age_unit) {
            'days' => $dob->copy()->addDays($this->max_age_value),
            'weeks' => $dob->copy()->addWeeks($this->max_age_value),
            'months' => $dob->copy()->addMonths($this->max_age_value),
            'years' => $dob->copy()->addYears($this->max_age_value),
            default => $dob->copy()->addMonths($this->max_age_value),
        };
    }
}

