<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'appointment_reminder_enabled',
        'appointment_reminder_hours',
        'appointment_reminder_template',
        'vaccination_reminder_enabled',
        'vaccination_reminder_days',
        'vaccination_reminder_template',
        'follow_up_reminder_enabled',
        'follow_up_reminder_days',
        'follow_up_reminder_template',
        'whatsapp_enabled',
    ];

    protected $casts = [
        'appointment_reminder_enabled' => 'boolean',
        'appointment_reminder_hours' => 'integer',
        'vaccination_reminder_enabled' => 'boolean',
        'vaccination_reminder_days' => 'integer',
        'follow_up_reminder_enabled' => 'boolean',
        'follow_up_reminder_days' => 'integer',
        'whatsapp_enabled' => 'boolean',
    ];

    /**
     * Default templates with placeholders.
     */
    public const DEFAULT_TEMPLATES = [
        'appointment_reminder' => "Hello {patient_name},\nThis is a reminder for your appointment on {appointment_date} at {appointment_time} with Dr. {doctor_name}.\n— {clinic_name}",
        'vaccination_reminder' => "Hello {patient_name},\nYour child's vaccination ({vaccine_name}) is due on {due_date}. Please visit {clinic_name} to get vaccinated.\n— {clinic_name}",
        'follow_up_reminder' => "Hello {patient_name},\nYou have a follow-up appointment scheduled on {appointment_date} at {appointment_time} with Dr. {doctor_name}.\n— {clinic_name}",
    ];

    /**
     * Boot: add global scope for clinic isolation.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('clinic', function (Builder $builder) {
            if (auth()->check() && auth()->user()->clinic_id && !auth()->user()->isSuperAdmin()) {
                $builder->where('notification_settings.clinic_id', auth()->user()->clinic_id);
            }
        });
    }

    /**
     * Get the clinic that owns these settings.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get effective template (stored or default).
     */
    public function getEffectiveTemplate(string $type): string
    {
        $column = "{$type}_template";
        return $this->$column ?: (self::DEFAULT_TEMPLATES[$type] ?? '');
    }

    /**
     * Get or create settings for a clinic.
     */
    public static function forClinic(int $clinicId): self
    {
        return static::withoutGlobalScopes()
            ->firstOrCreate(['clinic_id' => $clinicId]);
    }
}

