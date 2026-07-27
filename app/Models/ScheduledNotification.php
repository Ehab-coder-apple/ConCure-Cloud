<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class ScheduledNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'type',
        'channel',
        'scheduled_at',
        'status',
        'notifiable_type',
        'notifiable_id',
        'payload',
        'attempts',
        'last_attempted_at',
        'last_error',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'last_attempted_at' => 'datetime',
        'payload' => 'array',
        'attempts' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    const MAX_ATTEMPTS = 3;

    /**
     * Boot: add global scope for clinic isolation.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('clinic', function (Builder $builder) {
            if (auth()->check() && auth()->user()->clinic_id && !auth()->user()->isSuperAdmin()) {
                $builder->where('scheduled_notifications.clinic_id', auth()->user()->clinic_id);
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

    // --- Scopes (always use withoutGlobalScopes in CLI) ---

    public function scopeDue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('scheduled_at', '<=', now())
                     ->where('attempts', '<', self::MAX_ATTEMPTS);
    }

    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    // --- Helpers ---

    public function markProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markSent(): void
    {
        $this->update(['status' => self::STATUS_SENT]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => $this->attempts + 1 >= self::MAX_ATTEMPTS
                ? self::STATUS_FAILED
                : self::STATUS_PENDING,
            'attempts' => $this->attempts + 1,
            'last_attempted_at' => now(),
            'last_error' => $error,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }
}

