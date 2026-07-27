<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'type',
        'recipient',
        'subject',
        'message',
        'attachment_path',
        'status',
        'error_message',
        'external_id',
        'metadata',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Communication types
     */
    const TYPES = [
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email',
    ];

    /**
     * Communication statuses
     */
    const STATUSES = [
        'pending' => 'Pending',
        'sent' => 'Sent',
        'delivered' => 'Delivered',
        'failed' => 'Failed',
    ];

    /**
     * Get the patient that owns the communication log.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the clinic that owns the communication log.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who sent the communication.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge bg-warning',
            'sent' => 'badge bg-info',
            'delivered' => 'badge bg-success',
            'failed' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get the type icon class.
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'sms' => 'fas fa-sms',
            'whatsapp' => 'fab fa-whatsapp',
            'email' => 'fas fa-envelope',
            default => 'fas fa-comment',
        };
    }

    /**
     * Check if communication was successful.
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['sent', 'delivered']);
    }

    /**
     * Check if communication failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if communication is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
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
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter successful communications.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }

    /**
     * Scope to filter failed communications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to filter recent communications.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by sent date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('sent_at', 'desc')->orderBy('created_at', 'desc');
    }
}
