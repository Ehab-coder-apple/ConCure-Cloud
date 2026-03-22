<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PediatricPrescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'drug_id',
        'form_id',
        'rule_id',
        'clinic_id',
        'created_by',
        'patient_weight_kg',
        'patient_age_months',
        'dose_mg',
        'dose_ml',
        'recommended_dose_min_mg',
        'recommended_dose_max_mg',
        'frequency_per_day',
        'duration_days',
        'safety_status',
        'safety_message',
        'override_reason',
        'notes',
    ];

    protected $casts = [
        'patient_weight_kg' => 'decimal:2',
        'patient_age_months' => 'integer',
        'dose_mg' => 'decimal:2',
        'dose_ml' => 'decimal:2',
        'recommended_dose_min_mg' => 'decimal:2',
        'recommended_dose_max_mg' => 'decimal:2',
        'frequency_per_day' => 'integer',
        'duration_days' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(PediatricDrug::class, 'drug_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(PediatricDrugForm::class, 'form_id');
    }

    public function dosageRule(): BelongsTo
    {
        return $this->belongsTo(PediatricDosageRule::class, 'rule_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get safety status color.
     */
    public function getSafetyColorAttribute(): string
    {
        return match ($this->safety_status) {
            'safe' => '#10b981',
            'warning' => '#f59e0b',
            'danger' => '#ef4444',
            default => '#6b7280',
        };
    }

    /**
     * Get safety status icon.
     */
    public function getSafetyIconAttribute(): string
    {
        return match ($this->safety_status) {
            'safe' => '✅',
            'warning' => '⚠️',
            'danger' => '🔴',
            default => 'ℹ️',
        };
    }
}

