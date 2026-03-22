<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PediatricDosageRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'drug_id',
        'mg_per_kg_min',
        'mg_per_kg_max',
        'max_daily_mg',
        'frequency_per_day',
        'frequency_hours',
        'min_age_months',
        'max_age_months',
        'min_weight_kg',
        'max_weight_kg',
        'notes',
    ];

    protected $casts = [
        'mg_per_kg_min' => 'decimal:2',
        'mg_per_kg_max' => 'decimal:2',
        'max_daily_mg' => 'decimal:2',
        'frequency_per_day' => 'integer',
        'frequency_hours' => 'integer',
        'min_age_months' => 'integer',
        'max_age_months' => 'integer',
        'min_weight_kg' => 'decimal:2',
        'max_weight_kg' => 'decimal:2',
    ];

    public function drug(): BelongsTo
    {
        return $this->belongsTo(PediatricDrug::class, 'drug_id');
    }

    /**
     * Get the age range as a human-readable string.
     */
    public function getAgeRangeDisplayAttribute(): string
    {
        if ($this->min_age_months === null && $this->max_age_months === null) {
            return 'All ages';
        }

        $min = $this->min_age_months ?? 0;
        $max = $this->max_age_months;

        $minStr = $min >= 12 ? round($min / 12, 1) . 'y' : $min . 'mo';
        $maxStr = $max !== null ? ($max >= 12 ? round($max / 12, 1) . 'y' : $max . 'mo') : '∞';

        return $minStr . ' – ' . $maxStr;
    }

    /**
     * Check if this rule applies to a given age.
     */
    public function appliesToAge(?int $ageMonths): bool
    {
        if ($ageMonths === null) return true;
        if ($this->min_age_months !== null && $ageMonths < $this->min_age_months) return false;
        if ($this->max_age_months !== null && $ageMonths > $this->max_age_months) return false;
        return true;
    }
}

