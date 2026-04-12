<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AudiometryTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'ent_record_id',
        'test_date',
        'test_type',
        'right_ear_data',
        'left_ear_data',
        'right_srt',
        'left_srt',
        'right_wrs',
        'left_wrs',
        'right_tympanometry',
        'left_tympanometry',
        'right_interpretation',
        'left_interpretation',
        'notes',
        'recommendations',
        'performed_by',
        'created_by',
    ];

    protected $casts = [
        'test_date' => 'date',
        'right_ear_data' => 'array',
        'left_ear_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Test types
     */
    const TEST_TYPES = [
        'pure_tone' => 'Pure Tone Audiometry',
        'speech' => 'Speech Audiometry',
        'tympanometry' => 'Tympanometry',
        'other' => 'Other',
    ];

    /**
     * Interpretations
     */
    const INTERPRETATIONS = [
        'normal' => 'Normal Hearing',
        'conductive_loss' => 'Conductive Hearing Loss',
        'sensorineural_loss' => 'Sensorineural Hearing Loss',
        'mixed_loss' => 'Mixed Hearing Loss',
    ];

    /**
     * Standard audiometry frequencies (Hz)
     */
    const FREQUENCIES = [250, 500, 1000, 2000, 3000, 4000, 6000, 8000];

    /**
     * Get the patient that owns this audiometry test.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the clinic that owns this audiometry test.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the ENT record this test belongs to.
     */
    public function entRecord(): BelongsTo
    {
        return $this->belongsTo(EntRecord::class);
    }

    /**
     * Get the user who performed the test.
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get the user who created this record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get test type display name.
     */
    public function getTestTypeDisplayAttribute(): string
    {
        return self::TEST_TYPES[$this->test_type] ?? $this->test_type;
    }

    /**
     * Get right interpretation display name.
     */
    public function getRightInterpretationDisplayAttribute(): ?string
    {
        return $this->right_interpretation ? self::INTERPRETATIONS[$this->right_interpretation] ?? $this->right_interpretation : null;
    }

    /**
     * Get left interpretation display name.
     */
    public function getLeftInterpretationDisplayAttribute(): ?string
    {
        return $this->left_interpretation ? self::INTERPRETATIONS[$this->left_interpretation] ?? $this->left_interpretation : null;
    }

    /**
     * Scope by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
