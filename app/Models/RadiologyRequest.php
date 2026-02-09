<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiologyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'patient_id',
        'doctor_id',
        'clinical_notes',
        'clinical_history',
        'suspected_diagnosis',
        'requested_date',
        'due_date',
        'status',
        'priority',
        'radiology_center_name',
        'radiology_center_phone',
        'radiology_center_whatsapp',
        'radiology_center_email',
        'radiology_center_address',
        'communication_method',
        'communication_notes',
        'sent_at',
        'result_file_path',
        'radiologist_report',
        'findings',
        'impression',
        'result_received_at',
        'result_received_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'result_received_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     */
    protected $appends = [
        'communication_method_display',
        'status_display',
        'priority_display',
        'status_badge_class',
        'priority_badge_class',
    ];

    /**
     * Status options
     */
    const STATUSES = [
        'pending' => 'Pending',
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Priority options
     */
    const PRIORITIES = [
        'normal' => 'Normal',
        'urgent' => 'Urgent',
        'stat' => 'STAT',
    ];

    /**
     * Communication methods
     */
    const COMMUNICATION_METHODS = [
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
        'phone' => 'Phone',
        'in_person' => 'In Person',
    ];

    /**
     * Get the patient that owns this radiology request.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor who created this radiology request.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the tests for this radiology request.
     */
    public function tests(): HasMany
    {
        return $this->hasMany(RadiologyRequestTest::class);
    }

    /**
     * Get the user who received the results.
     */
    public function resultReceiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'result_received_by');
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
     * Get the communication method display name.
     */
    public function getCommunicationMethodDisplayAttribute(): string
    {
        return $this->communication_method ? 
               (self::COMMUNICATION_METHODS[$this->communication_method] ?? $this->communication_method) : 
               'Not specified';
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge bg-warning',
            'scheduled' => 'badge bg-info',
            'in_progress' => 'badge bg-primary',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get the priority badge class.
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            'normal' => 'badge bg-secondary',
            'urgent' => 'badge bg-warning',
            'stat' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Generate a unique request number.
     */
    public static function generateRequestNumber(): string
    {
        do {
            $number = 'RAD-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('request_number', $number)->exists());

        return $number;
    }

    /**
     * Add test to radiology request.
     */
    public function addTest(array $testData): RadiologyRequestTest
    {
        return $this->tests()->create($testData);
    }

    /**
     * Mark request as sent.
     */
    public function markAsSent(string $method, string $notes = null): void
    {
        $this->update([
            'communication_method' => $method,
            'communication_notes' => $notes,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark request as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Check if request has results.
     */
    public function hasResults(): bool
    {
        return !empty($this->result_file_path) || 
               !empty($this->radiologist_report) || 
               !empty($this->findings) || 
               !empty($this->impression);
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
     * Scope to filter by doctor.
     */
    public function scopeByDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->whereHas('patient', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        });
    }

    /**
     * Scope to search radiology requests.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('request_number', 'like', "%{$search}%")
              ->orWhere('clinical_notes', 'like', "%{$search}%")
              ->orWhere('suspected_diagnosis', 'like', "%{$search}%")
              ->orWhereHas('patient', function ($patientQuery) use ($search) {
                  $patientQuery->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('patient_id', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = self::generateRequestNumber();
            }
            if (empty($model->requested_date)) {
                $model->requested_date = now()->toDateString();
            }
        });
    }
}
