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
        'created_by',
        'clinic_id',
        'prescription_number',
        'diagnosis',
        'visit_type',
        'notes',
        'prescribed_date',
        'status',
        'sent_to_doctor',
        'sent_to_doctor_at',
        'reviewed_at',
        'invoice_id',
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
        'sent_to_doctor' => 'boolean',
        'sent_to_doctor_at' => 'datetime',
        'reviewed_at' => 'datetime',
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
     * The staff member (e.g. clinical assistant) who created this visit,
     * which may differ from `doctor_id` when the visit was sent to a
     * doctor for review.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The billing invoice generated for this visit's direct cost, if any.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Whether this visit is still waiting on the assigned doctor's review.
     */
    public function isPendingDoctorReview(): bool
    {
        return (bool) $this->sent_to_doctor && !$this->reviewed_at;
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
