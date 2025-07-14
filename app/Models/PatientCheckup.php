<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCheckup extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'weight',
        'height',
        'bmi',
        'blood_pressure',
        'heart_rate',
        'temperature',
        'respiratory_rate',
        'blood_sugar',
        'symptoms',
        'notes',
        'recommendations',
        'recorded_by',
        'checkup_date',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
        'temperature' => 'decimal:1',
        'blood_sugar' => 'decimal:2',
        'checkup_date' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($checkup) {
            // Calculate BMI if height and weight are provided
            if ($checkup->height && $checkup->weight) {
                $checkup->bmi = Patient::calculateBMI($checkup->weight, $checkup->height);
            }
            
            // Set checkup date if not provided
            if (!$checkup->checkup_date) {
                $checkup->checkup_date = now();
            }
        });

        static::updating(function ($checkup) {
            // Recalculate BMI if height or weight changed
            if ($checkup->isDirty(['height', 'weight']) && $checkup->height && $checkup->weight) {
                $checkup->bmi = Patient::calculateBMI($checkup->weight, $checkup->height);
            }
        });
    }

    /**
     * Get the patient that owns the checkup.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who recorded this checkup.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get blood pressure status.
     */
    public function getBloodPressureStatusAttribute(): string
    {
        if (!$this->blood_pressure) {
            return 'Unknown';
        }

        // Parse blood pressure (e.g., "120/80")
        $parts = explode('/', $this->blood_pressure);
        if (count($parts) !== 2) {
            return 'Invalid';
        }

        $systolic = (int) $parts[0];
        $diastolic = (int) $parts[1];

        if ($systolic < 90 || $diastolic < 60) {
            return 'Low';
        } elseif ($systolic < 120 && $diastolic < 80) {
            return 'Normal';
        } elseif ($systolic < 130 && $diastolic < 80) {
            return 'Elevated';
        } elseif ($systolic < 140 || $diastolic < 90) {
            return 'High Stage 1';
        } elseif ($systolic < 180 || $diastolic < 120) {
            return 'High Stage 2';
        } else {
            return 'Hypertensive Crisis';
        }
    }

    /**
     * Get heart rate status.
     */
    public function getHeartRateStatusAttribute(): string
    {
        if (!$this->heart_rate) {
            return 'Unknown';
        }

        if ($this->heart_rate < 60) {
            return 'Low (Bradycardia)';
        } elseif ($this->heart_rate <= 100) {
            return 'Normal';
        } else {
            return 'High (Tachycardia)';
        }
    }

    /**
     * Get temperature status.
     */
    public function getTemperatureStatusAttribute(): string
    {
        if (!$this->temperature) {
            return 'Unknown';
        }

        if ($this->temperature < 36.1) {
            return 'Low';
        } elseif ($this->temperature <= 37.2) {
            return 'Normal';
        } elseif ($this->temperature <= 38.0) {
            return 'Mild Fever';
        } elseif ($this->temperature <= 39.0) {
            return 'Moderate Fever';
        } else {
            return 'High Fever';
        }
    }

    /**
     * Get BMI category.
     */
    public function getBmiCategoryAttribute(): string
    {
        if (!$this->bmi) {
            return 'Unknown';
        }

        if ($this->bmi < 18.5) {
            return 'Underweight';
        } elseif ($this->bmi < 25) {
            return 'Normal weight';
        } elseif ($this->bmi < 30) {
            return 'Overweight';
        } else {
            return 'Obese';
        }
    }

    /**
     * Check if any vital signs are abnormal.
     */
    public function hasAbnormalVitals(): bool
    {
        $abnormalStatuses = [
            'Low', 'High', 'Elevated', 'High Stage 1', 'High Stage 2', 
            'Hypertensive Crisis', 'Low (Bradycardia)', 'High (Tachycardia)',
            'Mild Fever', 'Moderate Fever', 'High Fever'
        ];

        return in_array($this->blood_pressure_status, $abnormalStatuses) ||
               in_array($this->heart_rate_status, $abnormalStatuses) ||
               in_array($this->temperature_status, $abnormalStatuses);
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('checkup_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter recent checkups.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('checkup_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by checkup date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('checkup_date', 'desc');
    }
}
