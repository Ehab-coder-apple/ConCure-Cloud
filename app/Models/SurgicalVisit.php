<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurgicalVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'surgical_case_id',
        'visit_date',
        'visit_number',
        'clinical_observations',
        'wound_status',
        'wound_assessment',
        'medications_prescribed',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'wound_assessment' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function surgicalCase(): BelongsTo
    {
        return $this->belongsTo(SurgicalCase::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Methods

    /**
     * Format visit date for display
     *
     * @param string $format
     * @return string|null
     */
    public function getFormattedVisitDateAttribute($format = 'M d, Y'): ?string
    {
        return $this->visit_date?->format($format);
    }

    /**
     * Get formatted visit date (short format)
     *
     * @return string|null
     */
    public function getFormattedVisitDateShortAttribute(): ?string
    {
        return $this->visit_date?->format('m/d/Y');
    }
}
