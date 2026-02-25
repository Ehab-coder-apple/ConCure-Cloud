<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalLabRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'patient_id',
        'dental_treatment_id',
        'doctor_id',
	    'assigned_technician_id',
        'clinic_id',
        'external_lab_id',
        'work_type',
        'tooth_number',
        'tooth_numbers',
	    'quantity',
        'shade',
        'material',
        'specifications',
        'special_instructions',
        'requested_date',
        'due_date',
        'received_date',
        'status',
        'priority',
        'sent_at',
        'communication_method',
        'communication_notes',
        'estimated_cost',
        'actual_cost',
        'currency',
        'prescription_file_path',
        'impression_file_path',
        'result_file_path',
        'received_by',
        'notes',
        'quality_notes',
    ];

    protected $casts = [
        'tooth_numbers' => 'array',
	    'quantity' => 'integer',
        'requested_date' => 'date',
        'due_date' => 'date',
        'received_date' => 'date',
        'sent_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Work types
     */
    const WORK_TYPES = [
        'crown' => 'Crown',
        'bridge' => 'Bridge',
        'denture_full' => 'Full Denture',
        'denture_partial' => 'Partial Denture',
        'implant_crown' => 'Implant Crown',
        'implant_bridge' => 'Implant Bridge',
        'veneer' => 'Veneer',
        'inlay_onlay' => 'Inlay/Onlay',
        'orthodontic_appliance' => 'Orthodontic Appliance',
        'night_guard' => 'Night Guard',
        'sports_guard' => 'Sports Guard',
        'temporary_crown' => 'Temporary Crown',
        'other' => 'Other',
    ];

    /**
     * Materials
     */
    const MATERIALS = [
        'porcelain' => 'Porcelain',
        'zirconia' => 'Zirconia',
        'emax' => 'E-max',
        'metal' => 'Metal',
        'pfm' => 'Porcelain-Fused-to-Metal',
        'acrylic' => 'Acrylic',
        'composite' => 'Composite',
        'gold' => 'Gold',
        'other' => 'Other',
    ];

    /**
     * Statuses
     */
    const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Priorities
     */
    const PRIORITIES = [
        'normal' => 'Normal',
        'urgent' => 'Urgent',
        'rush' => 'Rush',
    ];

    /**
     * Communication methods
     */
    const COMMUNICATION_METHODS = [
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
        'phone' => 'Phone',
        'manual' => 'Manual Delivery',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (!$request->request_number) {
                $request->request_number = self::generateRequestNumber();
            }
        });
    }

    /**
     * Generate unique request number.
     */
    public static function generateRequestNumber(): string
    {
        $date = now()->format('Ymd');
        $random = rand(1000, 9999);
        $number = "DLR-{$date}-{$random}";

        // Ensure uniqueness
        while (self::where('request_number', $number)->exists()) {
            $random = rand(1000, 9999);
            $number = "DLR-{$date}-{$random}";
        }

        return $number;
    }

    /**
     * Get the patient.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the dental treatment.
     */
    public function dentalTreatment(): BelongsTo
    {
        return $this->belongsTo(DentalTreatment::class);
    }

    /**
     * Get the doctor.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

	/**
	 * Get the assigned technician (Dental Technician / CAD-CAM Designer).
	 */
	public function assignedTechnician(): BelongsTo
	{
	    return $this->belongsTo(User::class, 'assigned_technician_id');
	}

    /**
     * Get the clinic.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the external lab.
     */
    public function externalLab(): BelongsTo
    {
        return $this->belongsTo(ExternalLab::class);
    }

    /**
     * Get the user who received the lab work.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get work type display name.
     */
    public function getWorkTypeDisplayAttribute(): string
    {
        return self::WORK_TYPES[$this->work_type] ?? $this->work_type ?? '';
    }

    /**
     * Get material display name.
     */
    public function getMaterialDisplayAttribute(): string
    {
        return self::MATERIALS[$this->material] ?? $this->material ?? '';
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status ?? '';
    }

    /**
     * Get priority display name.
     */
    public function getPriorityDisplayAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority ?? '';
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge bg-warning',
            'in_progress' => 'badge bg-primary',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get priority badge class.
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            'normal' => 'badge bg-secondary',
            'urgent' => 'badge bg-warning',
            'rush' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
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
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by doctor.
     */
    public function scopeByDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope to search requests.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('request_number', 'like', "%{$search}%")
              ->orWhereHas('patient', function ($patientQuery) use ($search) {
                  $patientQuery->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('patient_id', 'like', "%{$search}%")
                              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
              })
              ->orWhereHas('externalLab', function ($labQuery) use ($search) {
                  $labQuery->where('name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope to restrict visibility based on assignment.
     *
     * Rules:
     * - Admin-like users (super/master/admin/program_owner) can see all.
     * - Dental technicians / CAD-CAM designers can see only requests assigned to them.
     * - All other roles can see only unassigned requests.
     */
    public function scopeVisibleTo($query, User $user)
    {
        // Admin-like roles can see all requests in whatever base query is already applied.
        if ($user->isSuperAdmin() || $user->isMasterAdmin() || in_array($user->role, ['admin', 'program_owner'])) {
            return $query;
        }

        // Assigned technicians see only their assigned requests.
        if (in_array($user->role, ['dental_technician', 'cad_cam_designer'])) {
            return $query->where('assigned_technician_id', $user->id);
        }

        // Everyone else cannot see assigned requests.
        return $query->whereNull('assigned_technician_id');
    }
}
