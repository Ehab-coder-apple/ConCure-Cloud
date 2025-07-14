<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'patient_id',
        'doctor_id',
        'clinical_notes',
        'requested_date',
        'due_date',
        'status',
        'priority',
        'lab_name',
        'notes',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Lab request statuses
     */
    const STATUSES = [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Lab request priorities
     */
    const PRIORITIES = [
        'normal' => 'Normal',
        'urgent' => 'Urgent',
        'stat' => 'STAT',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($labRequest) {
            if (!$labRequest->request_number) {
                $labRequest->request_number = self::generateRequestNumber();
            }
            
            if (!$labRequest->requested_date) {
                $labRequest->requested_date = now()->toDateString();
            }
        });
    }

    /**
     * Get the patient that owns the lab request.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor who created this lab request.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the tests for this lab request.
     */
    public function tests(): HasMany
    {
        return $this->hasMany(LabRequestTest::class);
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
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge bg-warning',
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
            $number = 'LAB-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('request_number', $number)->exists());

        return $number;
    }

    /**
     * Add test to lab request.
     */
    public function addTest(array $testData): LabRequestTest
    {
        return $this->tests()->create($testData);
    }

    /**
     * Check if lab request can be modified.
     */
    public function canBeModified(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark lab request as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark lab request as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Check if lab request is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->status === 'pending';
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
     * Scope to filter pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter overdue requests.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now());
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('requested_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter recent requests.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('requested_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by request date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('requested_date', 'desc');
    }
}
