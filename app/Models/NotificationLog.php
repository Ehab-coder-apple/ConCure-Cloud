<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'type',
        'channel',
        'recipient',
        'message',
        'status',
        'error_message',
        'external_id',
        'notifiable_type',
        'notifiable_id',
        'metadata',
        'sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    const TYPE_APPOINTMENT = 'appointment_reminder';
    const TYPE_VACCINATION = 'vaccination_reminder';
    const TYPE_FOLLOW_UP = 'follow_up_reminder';

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';

    /**
     * Boot: add global scope for clinic isolation.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('clinic', function (Builder $builder) {
            if (auth()->check() && auth()->user()->clinic_id && !auth()->user()->isSuperAdmin()) {
                $builder->where('notification_logs.clinic_id', auth()->user()->clinic_id);
            }
        });
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // --- Scopes ---

    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // --- Helpers ---

    public function markSent(?string $externalId = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'external_id' => $externalId,
            'sent_at' => now(),
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
        ]);
    }
}

