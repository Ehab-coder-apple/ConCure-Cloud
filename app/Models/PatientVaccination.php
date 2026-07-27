<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientVaccination extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'vaccine_id',
        'dose_number',
        'scheduled_date',
        'given_date',
        'status',
        'delay_days',
        'batch_number',
        'notes',
        'administered_by',
        'recorded_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'given_date' => 'date',
        'dose_number' => 'integer',
        'delay_days' => 'integer',
    ];

    public const STATUS_ON_TIME = 'on_time';
    public const STATUS_DELAYED = 'delayed';
    public const STATUS_MISSED = 'missed';
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_SKIPPED = 'skipped';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(Vaccine::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Check if this vaccination is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_MISSED, self::STATUS_DELAYED]);
    }

    /**
     * Get a CSS-friendly color class for the status.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ON_TIME => 'success',
            self::STATUS_DELAYED => 'warning',
            self::STATUS_MISSED => 'danger',
            self::STATUS_UPCOMING => 'secondary',
            self::STATUS_SKIPPED => 'dark',
            default => 'secondary',
        };
    }
}

