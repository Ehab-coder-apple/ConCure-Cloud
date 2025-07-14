<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_number',
        'patient_id',
        'doctor_id',
        'clinic_id',
        'diagnosis',
        'notes',
        'prescribed_date',
        'status',
    ];

    protected $casts = [
        'prescribed_date' => 'date',
    ];

    /**
     * Prescription statuses
     */
    const STATUSES = [
        'active' => 'Active',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prescription) {
            if (!$prescription->prescription_number) {
                $prescription->prescription_number = self::generatePrescriptionNumber();
            }
            
            if (!$prescription->prescribed_date) {
                $prescription->prescribed_date = now()->toDateString();
            }
        });
    }

    /**
     * Get the clinic that owns the prescription.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the patient that owns the prescription.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor who created this prescription.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the medicines for this prescription.
     */
    public function medicines(): HasMany
    {
        return $this->hasMany(PrescriptionMedicine::class);
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
            'active' => 'badge bg-success',
            'completed' => 'badge bg-primary',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Generate a unique prescription number.
     */
    public static function generatePrescriptionNumber(): string
    {
        do {
            $number = 'RX-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('prescription_number', $number)->exists());

        return $number;
    }

    /**
     * Add medicine to prescription.
     */
    public function addMedicine(array $medicineData): PrescriptionMedicine
    {
        return $this->medicines()->create($medicineData);
    }

    /**
     * Check if prescription can be modified.
     */
    public function canBeModified(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Mark prescription as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark prescription as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
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
     * Scope to filter active prescriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('prescribed_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter recent prescriptions.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('prescribed_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by prescription date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('prescribed_date', 'desc');
    }
}
