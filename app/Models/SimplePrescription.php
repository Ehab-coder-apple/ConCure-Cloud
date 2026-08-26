<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimplePrescription extends Model
{
    use HasFactory;

    protected $table = 'simple_prescriptions';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'clinic_id',
        'prescription_number',
        'diagnosis',
        'visit_type',
        'notes',
        'prescribed_date',
        'status',
        'is_dispensed',
        'dispensed_at',
        'dispensed_by',
        'dispense_reference',
    ];

    /**
     * Visit type options for the Quick Visit one-page workflow.
     */
    const VISIT_TYPES = [
        'new_visit' => 'New Visit',
        'follow_up' => 'Follow-up',
        'consultation' => 'Consultation',
        'emergency' => 'Emergency',
        'other' => 'Other',
    ];

    protected $casts = [
        'prescribed_date' => 'date',
        'is_dispensed' => 'boolean',
        'dispensed_at' => 'datetime',
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the pharmacist who dispensed this prescription.
     */
    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    /**
     * Check if prescription has been dispensed.
     */
    public function isDispensed(): bool
    {
        return $this->is_dispensed;
    }

    /**
     * Check if prescription can be dispensed.
     */
    public function canBeDispensed(): bool
    {
        return !$this->is_dispensed && $this->status === 'active';
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(SimplePrescriptionMedicine::class, 'prescription_id');
    }

    // Generate prescription number
    public static function generatePrescriptionNumber(): string
    {
        return 'RX-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    // Scope for clinic
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }
}
