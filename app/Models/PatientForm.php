<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PatientForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'form_template_id',
        'assigned_by_user_id',
        'assigned_at',
        'filled_by_user_id',
        'completed_at',
        'status',
        'form_data',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'form_data' => 'array',
    ];

    // Relationships
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function filledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by_user_id');
    }

    // Scopes
    public function scopeForClinic($query, ?int $clinicId)
    {
        if ($clinicId === null) {
            return $query->whereRaw('1 = 0');
        }
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Status helpers
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    public function markInProgress(): void
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            $this->status = self::STATUS_IN_PROGRESS;
            if (!$this->assigned_at) {
                $this->assigned_at = now();
            }
            $this->save();
        }
    }

    public function markCompleted(int $filledByUserId, array $data = []): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->filled_by_user_id = $filledByUserId;
        $this->completed_at = now();
        $this->form_data = $data; // cast handles JSON
        $this->save();
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}

