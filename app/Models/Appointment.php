<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'patient_id',
        'doctor_id',
        'clinic_id',
        'appointment_datetime',
        'duration_minutes',
        'type',
        'status',
        'reason',
        'notes',
        'diagnosis',
        'treatment',
        'send_reminder',
        'reminder_sent_at',
        'created_by',
    ];

    protected $casts = [
        'appointment_datetime' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'send_reminder' => 'boolean',
    ];

    /**
     * Appointment types
     */
    const TYPES = [
        'consultation' => 'Consultation',
        'follow_up' => 'Follow Up',
        'checkup' => 'Checkup',
        'procedure' => 'Procedure',
        'other' => 'Other',
    ];

    /**
     * Appointment statuses
     */
    const STATUSES = [
        'scheduled' => 'Scheduled',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (!$appointment->appointment_number) {
                $appointment->appointment_number = self::generateAppointmentNumber();
            }
        });
    }

    /**
     * Generate a unique appointment number.
     */
    public static function generateAppointmentNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get the last appointment number for this month
        $lastAppointment = self::where('appointment_number', 'like', "APT-{$year}{$month}-%")
            ->orderBy('appointment_number', 'desc')
            ->first();

        if ($lastAppointment) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastAppointment->appointment_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('APT-%s%s-%04d', $year, $month, $nextNumber);
    }

    /**
     * Get the patient that owns the appointment.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor that owns the appointment.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the clinic that owns the appointment.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this appointment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
     * Get the appointment date formatted.
     */
    public function getAppointmentDateAttribute(): string
    {
        return $this->appointment_datetime->format('Y-m-d');
    }

    /**
     * Get the appointment time formatted.
     */
    public function getAppointmentTimeAttribute(): string
    {
        return $this->appointment_datetime->format('H:i');
    }

    /**
     * Get the end time of the appointment.
     */
    public function getEndTimeAttribute(): Carbon
    {
        return $this->appointment_datetime->addMinutes($this->duration_minutes);
    }

    /**
     * Check if appointment is today.
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->appointment_datetime->isToday();
    }

    /**
     * Check if appointment is upcoming.
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->appointment_datetime->isFuture();
    }

    /**
     * Check if appointment is past.
     */
    public function getIsPastAttribute(): bool
    {
        return $this->appointment_datetime->isPast();
    }

    /**
     * Check if appointment can be cancelled.
     */
    public function getCanBeCancelledAttribute(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']) && $this->is_upcoming;
    }

    /**
     * Check if appointment can be completed.
     */
    public function getCanBeCompletedAttribute(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed', 'in_progress']);
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
     * Scope to filter by date.
     */
    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('appointment_datetime', $date);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('appointment_datetime', [$startDate, $endDate]);
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
     * Scope to get today's appointments.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_datetime', Carbon::today());
    }

    /**
     * Scope to get upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_datetime', '>', Carbon::now());
    }

    /**
     * Scope to get past appointments.
     */
    public function scopePast($query)
    {
        return $query->where('appointment_datetime', '<', Carbon::now());
    }

    /**
     * Scope to order by appointment datetime.
     */
    public function scopeOrderByDateTime($query, string $direction = 'asc')
    {
        return $query->orderBy('appointment_datetime', $direction);
    }
}
