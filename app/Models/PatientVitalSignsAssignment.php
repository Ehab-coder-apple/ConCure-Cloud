<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PatientVitalSignsAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'custom_vital_sign_id',
        'medical_condition',
        'reason',
        'is_active',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the patient that owns this assignment.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the custom vital sign configuration.
     */
    public function customVitalSign()
    {
        return $this->belongsTo(CustomVitalSignsConfig::class, 'custom_vital_sign_id');
    }

    /**
     * Get the user who assigned this vital sign.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope to get active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get assignments for a specific patient.
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to get assignments by medical condition.
     */
    public function scopeByCondition($query, $condition)
    {
        return $query->where('medical_condition', $condition);
    }

    /**
     * Get formatted assignment date.
     */
    public function getFormattedAssignedDateAttribute(): string
    {
        return $this->assigned_at->format('M d, Y');
    }

    /**
     * Get days since assignment.
     */
    public function getDaysSinceAssignmentAttribute(): int
    {
        return $this->assigned_at->diffInDays(now());
    }

    /**
     * Check if assignment is recent (within 7 days).
     */
    public function isRecentAssignment(): bool
    {
        return $this->days_since_assignment <= 7;
    }

    /**
     * Get assignment status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'bg-secondary';
        }

        if ($this->isRecentAssignment()) {
            return 'bg-success';
        }

        return 'bg-primary';
    }

    /**
     * Get assignment status text.
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if ($this->isRecentAssignment()) {
            return 'Recently Added';
        }

        return 'Active';
    }

    /**
     * Assign vital signs to patient from medical condition template.
     */
    public static function assignFromTemplate(Patient $patient, MedicalConditionTemplate $template, User $assignedBy, string $reason = null): array
    {
        $assignments = [];
        
        foreach ($template->vital_sign_ids as $vitalSignId) {
            // Check if already assigned
            $existing = self::where('patient_id', $patient->id)
                           ->where('custom_vital_sign_id', $vitalSignId)
                           ->first();

            if (!$existing) {
                $assignment = self::create([
                    'patient_id' => $patient->id,
                    'custom_vital_sign_id' => $vitalSignId,
                    'medical_condition' => $template->condition_name,
                    'reason' => $reason ?: "Assigned from {$template->condition_name} template",
                    'is_active' => true,
                    'assigned_by' => $assignedBy->id,
                    'assigned_at' => now(),
                ]);

                $assignments[] = $assignment;
            } else {
                // Reactivate if inactive
                if (!$existing->is_active) {
                    $existing->update([
                        'is_active' => true,
                        'medical_condition' => $template->condition_name,
                        'reason' => $reason ?: "Reactivated from {$template->condition_name} template",
                        'assigned_by' => $assignedBy->id,
                        'assigned_at' => now(),
                    ]);
                    $assignments[] = $existing;
                }
            }
        }

        return $assignments;
    }

    /**
     * Assign individual vital sign to patient.
     */
    public static function assignVitalSign(Patient $patient, CustomVitalSignsConfig $vitalSign, User $assignedBy, string $condition = null, string $reason = null): self
    {
        // Check if already assigned
        $existing = self::where('patient_id', $patient->id)
                       ->where('custom_vital_sign_id', $vitalSign->id)
                       ->first();

        if ($existing) {
            // Reactivate if inactive
            if (!$existing->is_active) {
                $existing->update([
                    'is_active' => true,
                    'medical_condition' => $condition,
                    'reason' => $reason ?: 'Manually assigned',
                    'assigned_by' => $assignedBy->id,
                    'assigned_at' => now(),
                ]);
            }
            return $existing;
        }

        return self::create([
            'patient_id' => $patient->id,
            'custom_vital_sign_id' => $vitalSign->id,
            'medical_condition' => $condition,
            'reason' => $reason ?: 'Manually assigned',
            'is_active' => true,
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Get patient's active vital signs with their configurations.
     */
    public static function getPatientActiveVitalSigns(Patient $patient): \Illuminate\Database\Eloquent\Collection
    {
        return self::with('customVitalSign')
                  ->forPatient($patient->id)
                  ->active()
                  ->get()
                  ->sortBy('customVitalSign.sort_order');
    }

    /**
     * Remove vital sign assignment from patient.
     */
    public function deactivate(User $user, string $reason = null): bool
    {
        return $this->update([
            'is_active' => false,
            'reason' => $reason ?: 'Manually deactivated',
            'assigned_by' => $user->id,
        ]);
    }
}
