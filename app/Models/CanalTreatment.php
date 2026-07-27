<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CanalTreatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dental_treatment_id',
        'patient_id',
        'clinic_id',
        'tooth_number',
        'canal_name',
        'working_length',
        'master_apical_file',
        'master_cone_size',
        'taper',
        'irrigation_protocol',
        'obturation_technique',
        'sealer_type',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'working_length' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Canal treatment statuses
     */
    const STATUSES = [
        'not_started' => 'Not Started',
        'located' => 'Located',
        'instrumented' => 'Instrumented',
        'obturated' => 'Obturated',
        'completed' => 'Completed',
    ];

    /**
     * Common MAF sizes
     */
    const MAF_SIZES = ['08', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55', '60', '70', '80'];

    /**
     * Common taper values
     */
    const TAPERS = ['.02', '.04', '.06', '.08'];

    /**
     * Common irrigation protocols
     */
    const IRRIGATION_PROTOCOLS = [
        'NaOCl 2.5%',
        'NaOCl 5.25%',
        'NaOCl 5.25% + EDTA 17%',
        'NaOCl 5.25% + CHX 2%',
        'CHX 2%',
        'EDTA 17%',
        'Saline',
    ];

    /**
     * Common obturation techniques
     */
    const OBTURATION_TECHNIQUES = [
        'Lateral condensation',
        'Warm vertical condensation',
        'Single cone',
        'Continuous wave',
        'Thermoplasticized injection',
    ];

    /**
     * Common sealers
     */
    const SEALERS = [
        'AH Plus',
        'BioRoot RCS',
        'TotalFill BC Sealer',
        'Pulp Canal Sealer',
        'Sealapex',
        'EndoSequence BC Sealer',
    ];

    /**
     * Get the dental treatment this canal treatment belongs to.
     */
    public function dentalTreatment(): BelongsTo
    {
        return $this->belongsTo(DentalTreatment::class);
    }

    /**
     * Get the patient.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the clinic.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
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
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Scope by dental treatment.
     */
    public function scopeByTreatment($query, int $treatmentId)
    {
        return $query->where('dental_treatment_id', $treatmentId);
    }

    /**
     * Scope by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}

