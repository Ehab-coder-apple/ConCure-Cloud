<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DentalChart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'visit_id',
        'chart_type',
        'created_by',
        'general_notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Chart types
     */
    const CHART_TYPES = [
        'adult' => 'Adult (Permanent Dentition)',
        'pediatric' => 'Pediatric (Primary Dentition)',
    ];

    /**
     * Get the patient that owns this dental chart.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the clinic that owns this dental chart.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this dental chart.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the tooth records for this dental chart.
     */
    public function toothRecords(): HasMany
    {
        return $this->hasMany(DentalToothRecord::class);
    }

    /**
     * Get the treatments associated with this dental chart.
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(DentalTreatment::class);
    }

    /**
     * Get the images associated with this dental chart.
     */
    public function images(): HasMany
    {
        return $this->hasMany(DentalImage::class);
    }

    /**
     * Get the chart type display name.
     */
    public function getChartTypeDisplayAttribute(): string
    {
        return self::CHART_TYPES[$this->chart_type] ?? $this->chart_type;
    }

    /**
     * Get all tooth numbers for this chart type.
     */
    public function getToothNumbersAttribute(): array
    {
        if ($this->chart_type === 'pediatric') {
            return [
                'upper_right' => ['55', '54', '53', '52', '51'],
                'upper_left' => ['61', '62', '63', '64', '65'],
                'lower_left' => ['71', '72', '73', '74', '75'],
                'lower_right' => ['85', '84', '83', '82', '81'],
            ];
        }

        // Adult (permanent) dentition
        return [
            'upper_right' => ['18', '17', '16', '15', '14', '13', '12', '11'],
            'upper_left' => ['21', '22', '23', '24', '25', '26', '27', '28'],
            'lower_left' => ['31', '32', '33', '34', '35', '36', '37', '38'],
            'lower_right' => ['48', '47', '46', '45', '44', '43', '42', '41'],
        ];
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by chart type.
     */
    public function scopeByChartType($query, string $chartType)
    {
        return $query->where('chart_type', $chartType);
    }

    /**
     * Get the latest chart for a patient.
     */
    public static function getLatestForPatient(int $patientId): ?self
    {
        return self::where('patient_id', $patientId)
                   ->latest()
                   ->first();
    }
}

