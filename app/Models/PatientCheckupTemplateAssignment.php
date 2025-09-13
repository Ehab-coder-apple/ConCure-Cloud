<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PatientCheckupTemplateAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'template_id',
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
     * Get the custom checkup template.
     */
    public function template()
    {
        return $this->belongsTo(CustomCheckupTemplate::class, 'template_id');
    }

    /**
     * Get the user who assigned this template.
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
     * Assign checkup template to patient.
     */
    public static function assignTemplate(Patient $patient, CustomCheckupTemplate $template, User $assignedBy, string $condition = null, string $reason = null): self
    {
        // Check if already assigned
        $existing = self::where('patient_id', $patient->id)
                       ->where('template_id', $template->id)
                       ->first();

        if ($existing) {
            // Reactivate if inactive
            if (!$existing->is_active) {
                $existing->update([
                    'is_active' => true,
                    'medical_condition' => $condition,
                    'reason' => $reason ?: 'Reactivated template assignment',
                    'assigned_by' => $assignedBy->id,
                    'assigned_at' => now(),
                ]);
            }
            return $existing;
        }

        return self::create([
            'patient_id' => $patient->id,
            'template_id' => $template->id,
            'medical_condition' => $condition,
            'reason' => $reason ?: 'Template assigned for specialized checkups',
            'is_active' => true,
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Get patient's active checkup templates.
     */
    public static function getPatientActiveTemplates(Patient $patient): \Illuminate\Database\Eloquent\Collection
    {
        return self::with('template')
                  ->forPatient($patient->id)
                  ->active()
                  ->get()
                  ->sortBy('template.name');
    }

    /**
     * Remove template assignment from patient.
     */
    public function deactivate(User $user, string $reason = null): bool
    {
        return $this->update([
            'is_active' => false,
            'reason' => $reason ?: 'Manually deactivated',
            'assigned_by' => $user->id,
        ]);
    }

    /**
     * Get checkups created using this template assignment.
     */
    public function getCheckupsUsingTemplateAttribute()
    {
        return PatientCheckup::where('patient_id', $this->patient_id)
                            ->where('template_id', $this->template_id)
                            ->orderBy('checkup_date', 'desc')
                            ->get();
    }

    /**
     * Get usage statistics for this assignment.
     */
    public function getUsageStatsAttribute(): array
    {
        $checkups = $this->checkups_using_template;
        $totalCheckups = $checkups->count();
        $recentCheckups = $checkups->where('checkup_date', '>=', now()->subDays(30))->count();

        return [
            'total_checkups' => $totalCheckups,
            'recent_checkups' => $recentCheckups,
            'last_used' => $totalCheckups > 0 ? $checkups->first()->checkup_date->format('M d, Y') : null,
            'usage_frequency' => $this->days_since_assignment > 0 ? round($totalCheckups / ($this->days_since_assignment / 30), 1) : 0,
        ];
    }

    /**
     * Check if template has been used recently.
     */
    public function hasRecentUsage(): bool
    {
        return PatientCheckup::where('patient_id', $this->patient_id)
                            ->where('template_id', $this->template_id)
                            ->where('checkup_date', '>=', now()->subDays(30))
                            ->exists();
    }

    /**
     * Get recommended templates for patient based on medical conditions.
     */
    public static function getRecommendedTemplates(Patient $patient)
    {
        $patientConditions = $patient->medical_conditions;

        if (empty($patientConditions)) {
            return CustomCheckupTemplate::query()->whereRaw('1 = 0')->get(); // Return empty Eloquent Collection
        }

        return CustomCheckupTemplate::forClinic($patient->clinic_id)
                                   ->active()
                                   ->whereIn('medical_condition', $patientConditions)
                                   ->whereNotIn('id', function($query) use ($patient) {
                                       $query->select('template_id')
                                             ->from('patient_checkup_template_assignments')
                                             ->where('patient_id', $patient->id)
                                             ->where('is_active', true);
                                   })
                                   ->get();
    }

    /**
     * Bulk assign templates to patient.
     */
    public static function bulkAssignTemplates(Patient $patient, array $templateIds, User $assignedBy, string $reason = null): array
    {
        $assignments = [];
        
        foreach ($templateIds as $templateId) {
            $template = CustomCheckupTemplate::find($templateId);
            if ($template && $template->clinic_id === $patient->clinic_id) {
                $assignment = self::assignTemplate(
                    $patient,
                    $template,
                    $assignedBy,
                    $template->medical_condition,
                    $reason
                );
                $assignments[] = $assignment;
            }
        }

        return $assignments;
    }
}
