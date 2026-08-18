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
        'fat_kg',
        'muscle_percentage',
        'muscle_kg',
        'waist_cm',
        'hip_cm',
        'waist_to_hip_ratio',
        'whr_direct',
        'visceral_fat',
        'mineral_kg',
        'body_water_percentage',
        'body_water_liters',
        'notes',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'bmi' => 'decimal:2',
        'fat_percentage' => 'decimal:2',
        'fat_kg' => 'decimal:2',
        'muscle_percentage' => 'decimal:2',
        'muscle_kg' => 'decimal:2',
        'waist_cm' => 'decimal:2',
        'hip_cm' => 'decimal:2',
        'waist_to_hip_ratio' => 'decimal:3',
        'whr_direct' => 'decimal:3',
        'visceral_fat' => 'decimal:2',
        'mineral_kg' => 'decimal:2',
        'body_water_percentage' => 'decimal:2',
        'body_water_liters' => 'decimal:2',
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
            $m->applyEffectiveWhr();
        });

        static::updating(function ($m) {
            if ($m->isDirty(['weight_kg', 'height_cm'])) {
                if ($m->weight_kg && $m->height_cm && $m->height_cm > 0) {
                    $heightM = $m->height_cm / 100;
                    $m->bmi = round($m->weight_kg / ($heightM * $heightM), 2);
                }
            }
            if ($m->isDirty(['waist_cm', 'hip_cm', 'whr_direct'])) {
                $m->applyEffectiveWhr();
            }
        });
    }

    /**
     * Populate waist_to_hip_ratio either from a directly-entered value
     * (whr_direct, e.g. from a body composition analyzer) or, if not
     * provided, calculated from waist/hip measurements.
     */
    public function applyEffectiveWhr(): void
    {
        if ($this->whr_direct) {
            $this->waist_to_hip_ratio = $this->whr_direct;
        } elseif ($this->waist_cm && $this->hip_cm && $this->hip_cm > 0) {
            $this->waist_to_hip_ratio = round($this->waist_cm / $this->hip_cm, 3);
        }
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

