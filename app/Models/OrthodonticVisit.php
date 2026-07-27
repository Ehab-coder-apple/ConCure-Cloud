<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrthodonticVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orthodontic_case_id',
        'patient_id',
        'clinic_id',
        'doctor_id',
        'visit_date',
        'visit_number',
        'visit_type',
        'procedures_performed',
        'observations',
        'patient_concerns',
        'oral_hygiene_status',
        'broken_brackets',
        'appliance_condition',
        'next_appointment_date',
        'instructions_given',
        'notes',
        'created_by',
        'updated_by',
        // Clinical Mechanics Fields
        'upper_wire',
        'lower_wire',
        'elastic_type',
        'power_chain',
        'coil_spring',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'next_appointment_date' => 'date',
        'broken_brackets' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const VISIT_TYPES = [
        'adjustment' => 'Regular Adjustment',
        'emergency' => 'Emergency',
        'review' => 'Progress Review',
        'installation' => 'Initial Installation',
        'removal' => 'Removal',
        'final' => 'Final Check',
    ];

    // Relationships

    public function orthodonticCase(): BelongsTo
    {
        return $this->belongsTo(OrthodonticCase::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(OrthodonticPhoto::class);
    }

    // Accessors

    public function getVisitTypeDisplayAttribute(): string
    {
        return self::VISIT_TYPES[$this->visit_type] ?? $this->visit_type;
    }
}
