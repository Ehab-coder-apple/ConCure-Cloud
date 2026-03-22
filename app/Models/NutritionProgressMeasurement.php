<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionProgressMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'recorded_by',
        'measurement_date',
        'weight_kg',
        'height_cm',
        'bmi',
        'fat_percentage',
        'muscle_percentage',
        'waist_cm',
        'hip_cm',
        'waist_to_hip_ratio',
        'visceral_fat',
        'body_water_percentage',
        'notes',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'bmi' => 'decimal:2',
        'fat_percentage' => 'decimal:2',
        'muscle_percentage' => 'decimal:2',
        'waist_cm' => 'decimal:2',
        'hip_cm' => 'decimal:2',
        'waist_to_hip_ratio' => 'decimal:3',
        'visceral_fat' => 'decimal:2',
        'body_water_percentage' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($m) {
            // Auto-calculate BMI
            if ($m->weight_kg && $m->height_cm && $m->height_cm > 0) {
                $heightM = $m->height_cm / 100;
                $m->bmi = round($m->weight_kg / ($heightM * $heightM), 2);
            }
            // Auto-calculate waist-to-hip ratio
            if ($m->waist_cm && $m->hip_cm && $m->hip_cm > 0) {
                $m->waist_to_hip_ratio = round($m->waist_cm / $m->hip_cm, 3);
            }
        });

        static::updating(function ($m) {
            if ($m->isDirty(['weight_kg', 'height_cm'])) {
                if ($m->weight_kg && $m->height_cm && $m->height_cm > 0) {
                    $heightM = $m->height_cm / 100;
                    $m->bmi = round($m->weight_kg / ($heightM * $heightM), 2);
                }
            }
            if ($m->isDirty(['waist_cm', 'hip_cm'])) {
                if ($m->waist_cm && $m->hip_cm && $m->hip_cm > 0) {
                    $m->waist_to_hip_ratio = round($m->waist_cm / $m->hip_cm, 3);
                }
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get BMI category string.
     */
    public function getBmiCategoryAttribute(): string
    {
        if (!$this->bmi) return 'Unknown';
        if ($this->bmi < 18.5) return 'Underweight';
        if ($this->bmi < 25) return 'Normal';
        if ($this->bmi < 30) return 'Overweight';
        return 'Obese';
    }

    /**
     * Get BMI category color.
     */
    public function getBmiColorAttribute(): string
    {
        if (!$this->bmi) return '#6b7280';
        if ($this->bmi < 18.5) return '#3b82f6';
        if ($this->bmi < 25) return '#10b981';
        if ($this->bmi < 30) return '#f59e0b';
        return '#ef4444';
    }

    /**
     * Get waist-to-hip risk category based on sex.
     */
    public function getWhrRiskAttribute(): string
    {
        if (!$this->waist_to_hip_ratio) return 'Unknown';
        $gender = $this->patient?->gender;
        if ($gender === 'male') {
            if ($this->waist_to_hip_ratio < 0.90) return 'Low';
            if ($this->waist_to_hip_ratio < 1.00) return 'Moderate';
            return 'High';
        }
        // Female defaults
        if ($this->waist_to_hip_ratio < 0.80) return 'Low';
        if ($this->waist_to_hip_ratio < 0.85) return 'Moderate';
        return 'High';
    }
}

