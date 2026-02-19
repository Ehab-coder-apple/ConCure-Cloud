<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DentalTreatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'treatment_number',
        'patient_id',
        'clinic_id',
        'dental_chart_id',
        'tooth_number',
        'tooth_numbers',
        'procedure_name',
        'procedure_code',
        'diagnosis',
        'icd10_code',
        'surfaces_affected',
        'description',
        'estimated_cost',
        'actual_cost',
        'currency',
        'estimated_duration_minutes',
        'status',
        'priority',
        'severity',
        'scheduled_date',
        'completed_date',
        'assigned_doctor_id',
        'performed_by_id',
        'payment_status',
        'paid_amount',
        'notes',
        'post_treatment_notes',
        'created_by',
    ];

    protected $casts = [
        'tooth_numbers' => 'array',
        'surfaces_affected' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Treatment statuses
     */
    const STATUSES = [
        'planned' => 'Planned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Treatment priorities
     */
    const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    /**
     * Payment statuses
     */
    const PAYMENT_STATUSES = [
        'unpaid' => 'Unpaid',
        'partial' => 'Partially Paid',
        'paid' => 'Paid',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($treatment) {
            if (!$treatment->treatment_number) {
                $treatment->treatment_number = self::generateTreatmentNumber();
            }
        });
    }

    /**
     * Generate unique treatment number.
     */
    public static function generateTreatmentNumber(): string
    {
        $date = now()->format('Ymd');
        $random = rand(1000, 9999);
        $number = "DT-{$date}-{$random}";

        // Ensure uniqueness
        while (self::where('treatment_number', $number)->exists()) {
            $random = rand(1000, 9999);
            $number = "DT-{$date}-{$random}";
        }

        return $number;
    }

    /**
     * Get the patient that owns this treatment.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the clinic that owns this treatment.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the dental chart associated with this treatment.
     */
    public function dentalChart(): BelongsTo
    {
        return $this->belongsTo(DentalChart::class);
    }

    /**
     * Get the assigned doctor.
     */
    public function assignedDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_doctor_id');
    }

    /**
     * Get the doctor who performed the treatment.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_id');
    }

    /**
     * Get the user who created this treatment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get dental lab requests for this treatment.
     */
    public function dentalLabRequests(): HasMany
    {
        return $this->hasMany(DentalLabRequest::class);
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the priority display name.
     */
    public function getPriorityDisplayAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    /**
     * Get the payment status display name.
     */
    public function getPaymentStatusDisplayAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Get the status badge class for UI.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'planned' => 'badge bg-secondary',
            'in_progress' => 'badge bg-primary',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get the priority badge class for UI.
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            'low' => 'badge bg-info',
            'medium' => 'badge bg-secondary',
            'high' => 'badge bg-warning',
            'urgent' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get remaining balance.
     */
    public function getRemainingBalanceAttribute(): float
    {
        $cost = $this->actual_cost ?? $this->estimated_cost ?? 0;
        return max(0, $cost - $this->paid_amount);
    }

    /**
     * Check if treatment is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->remaining_balance <= 0;
    }

    /**
     * Mark treatment as completed.
     */
    public function markAsCompleted(?int $performedById = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_date' => now(),
            'performed_by_id' => $performedById ?? auth()->id(),
        ]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
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
     * Scope to filter by assigned doctor.
     */
    public function scopeByDoctor($query, int $doctorId)
    {
        return $query->where('assigned_doctor_id', $doctorId);
    }

    /**
     * Scope to filter by payment status.
     */
    public function scopeByPaymentStatus($query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope to search treatments.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('treatment_number', 'like', "%{$search}%")
              ->orWhere('procedure_name', 'like', "%{$search}%")
              ->orWhere('diagnosis', 'like', "%{$search}%")
              ->orWhereHas('patient', function ($patientQuery) use ($search) {
                  $patientQuery->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('patient_id', 'like', "%{$search}%")
                              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
              });
        });
    }
}

