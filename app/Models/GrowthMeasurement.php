<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'measurement_date',
        'weight_kg',
        'length_height_cm',
        'head_circumference_cm',
        'bmi',
        'age_months',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'weight_kg' => 'decimal:3',
        'length_height_cm' => 'decimal:2',
        'head_circumference_cm' => 'decimal:2',
        'bmi' => 'decimal:2',
        'age_months' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($measurement) {
            // Auto-calculate BMI if weight and height are provided
            if ($measurement->weight_kg && $measurement->length_height_cm) {
                $heightM = $measurement->length_height_cm / 100;
                if ($heightM > 0) {
                    $measurement->bmi = round($measurement->weight_kg / ($heightM * $heightM), 2);
                }
            }

            // Auto-calculate age in months at measurement date
            if ($measurement->patient_id && $measurement->measurement_date) {
                $patient = Patient::find($measurement->patient_id);
                if ($patient && $patient->date_of_birth) {
                    $rawDob = $patient->getAttributes()['date_of_birth'] ?? $patient->getRawOriginal('date_of_birth');
                    if (!empty($rawDob) && $rawDob !== '0000-00-00') {
                        try {
                            $dob = \Carbon\Carbon::parse($rawDob);
                            $measureDate = \Carbon\Carbon::parse($measurement->measurement_date);
                            $measurement->age_months = round($dob->floatDiffInMonths($measureDate), 2);
                        } catch (\Exception $e) {
                            // skip
                        }
                    }
                }
            }
        });

        static::updating(function ($measurement) {
            if ($measurement->isDirty(['weight_kg', 'length_height_cm'])) {
                if ($measurement->weight_kg && $measurement->length_height_cm) {
                    $heightM = $measurement->length_height_cm / 100;
                    if ($heightM > 0) {
                        $measurement->bmi = round($measurement->weight_kg / ($heightM * $heightM), 2);
                    }
                }
            }
        });
    }

    /**
     * Get the patient that owns the measurement.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who created the measurement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, ?int $clinicId)
    {
        if ($clinicId) {
            return $query->where('clinic_id', $clinicId);
        }
        return $query;
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('measurement_date', '>=', $from);
        }
        if ($to) {
            $query->where('measurement_date', '<=', $to);
        }
        return $query;
    }
}

