<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'doctor_id',
        'visit_date',
        'chief_complaint',
        'ear_examination',
        'nose_examination',
        'throat_examination',
        'neck_examination',
        'cranial_nerves',
        'diagnosis',
        'icd10_code',
        'treatment_plan',
        'medications',
        'followup_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'followup_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the patient that owns this ENT record.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the clinic that owns this ENT record.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the doctor who performed the examination.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the user who created this record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get audiometry tests for this ENT record.
     */
    public function audiometryTests(): HasMany
    {
        return $this->hasMany(AudiometryTest::class);
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

    /**
     * Get the latest ENT record for a patient.
     */
    public static function getLatestForPatient(int $patientId): ?self
    {
        return self::where('patient_id', $patientId)
                   ->latest('visit_date')
                   ->first();
    }
}
