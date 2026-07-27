<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalConditionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'condition_name',
        'description',
        'vital_sign_ids',
        'specialty',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'vital_sign_ids' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the clinic that owns this template.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this template.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the custom vital signs associated with this template.
     */
    public function customVitalSigns()
    {
        return CustomVitalSignsConfig::whereIn('id', $this->vital_sign_ids ?? [])
                                    ->where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->get();
    }

    /**
     * Scope to get active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get templates for a specific clinic.
     */
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to get templates by specialty.
     */
    public function scopeBySpecialty($query, $specialty)
    {
        return $query->where('specialty', $specialty);
    }

    /**
     * Get the number of vital signs in this template.
     */
    public function getVitalSignsCountAttribute(): int
    {
        return count($this->vital_sign_ids ?? []);
    }

    /**
     * Get formatted vital signs list.
     */
    public function getFormattedVitalSignsAttribute(): string
    {
        $vitalSigns = $this->customVitalSigns();
        
        if ($vitalSigns->isEmpty()) {
            return 'No vital signs configured';
        }

        return $vitalSigns->pluck('name')->join(', ');
    }

    /**
     * Check if template has valid vital signs.
     */
    public function hasValidVitalSigns(): bool
    {
        if (empty($this->vital_sign_ids)) {
            return false;
        }

        $validCount = CustomVitalSignsConfig::whereIn('id', $this->vital_sign_ids)
                                           ->where('clinic_id', $this->clinic_id)
                                           ->where('is_active', true)
                                           ->count();

        return $validCount > 0;
    }

    /**
     * Get template usage statistics.
     */
    public function getUsageStatsAttribute(): array
    {
        $assignmentsCount = PatientVitalSignsAssignment::where('medical_condition', $this->condition_name)
                                                      ->count();

        $activeAssignmentsCount = PatientVitalSignsAssignment::where('medical_condition', $this->condition_name)
                                                            ->where('is_active', true)
                                                            ->count();

        return [
            'total_assignments' => $assignmentsCount,
            'active_assignments' => $activeAssignmentsCount,
            'usage_rate' => $assignmentsCount > 0 ? round(($activeAssignmentsCount / $assignmentsCount) * 100, 1) : 0,
        ];
    }

    /**
     * Get default medical condition templates.
     */
    public static function getDefaultTemplates(): array
    {
        return [
            [
                'condition_name' => 'Diabetes Management',
                'description' => 'Comprehensive monitoring for diabetic patients including blood sugar tracking and related assessments',
                'specialty' => 'Endocrinology',
                'vital_signs' => ['Blood Sugar Level', 'Weight', 'Blood Pressure'],
            ],
            [
                'condition_name' => 'Cardiac Monitoring',
                'description' => 'Heart condition monitoring including oxygen saturation and cardiac function assessment',
                'specialty' => 'Cardiology',
                'vital_signs' => ['Oxygen Saturation', 'Heart Rate Variability', 'Exercise Tolerance'],
            ],
            [
                'condition_name' => 'Pain Management',
                'description' => 'Comprehensive pain assessment and monitoring for chronic pain patients',
                'specialty' => 'Pain Management',
                'vital_signs' => ['Pain Level', 'Mobility Status', 'Sleep Quality'],
            ],
            [
                'condition_name' => 'Respiratory Care',
                'description' => 'Respiratory function monitoring for patients with breathing difficulties',
                'specialty' => 'Pulmonology',
                'vital_signs' => ['Oxygen Saturation', 'Peak Flow', 'Respiratory Rate'],
            ],
            [
                'condition_name' => 'Geriatric Assessment',
                'description' => 'Comprehensive elderly patient monitoring including cognitive and mobility assessments',
                'specialty' => 'Geriatrics',
                'vital_signs' => ['Mobility Status', 'Mental Status', 'Fall Risk Assessment'],
            ],
            [
                'condition_name' => 'Hypertension Management',
                'description' => 'Blood pressure monitoring and cardiovascular risk assessment',
                'specialty' => 'Cardiology',
                'vital_signs' => ['Blood Pressure', 'Weight', 'Sodium Intake'],
            ],
            [
                'condition_name' => 'Mental Health Assessment',
                'description' => 'Psychological and emotional well-being monitoring',
                'specialty' => 'Psychiatry',
                'vital_signs' => ['Anxiety Level', 'Mood Assessment', 'Sleep Quality'],
            ],
            [
                'condition_name' => 'Pediatric Development',
                'description' => 'Child development and growth monitoring',
                'specialty' => 'Pediatrics',
                'vital_signs' => ['Developmental Milestones', 'Growth Percentile', 'Feeding Status'],
            ],
            [
                'condition_name' => 'Post-Surgical Care',
                'description' => 'Post-operative monitoring and recovery assessment',
                'specialty' => 'Surgery',
                'vital_signs' => ['Pain Level', 'Wound Healing Status', 'Mobility Status'],
            ],
            [
                'condition_name' => 'Chronic Kidney Disease',
                'description' => 'Kidney function monitoring and related health assessments',
                'specialty' => 'Nephrology',
                'vital_signs' => ['Fluid Intake', 'Urine Output', 'Blood Pressure'],
            ],
        ];
    }

    /**
     * Create template with vital signs.
     */
    public static function createWithVitalSigns(array $data, array $vitalSignNames, User $creator): self
    {
        // Find vital sign IDs by names
        $vitalSignIds = CustomVitalSignsConfig::forClinic($creator->clinic_id)
                                             ->whereIn('name', $vitalSignNames)
                                             ->where('is_active', true)
                                             ->pluck('id')
                                             ->toArray();

        $data['vital_sign_ids'] = $vitalSignIds;
        $data['clinic_id'] = $creator->clinic_id;
        $data['created_by'] = $creator->id;
        $data['is_active'] = true;

        return self::create($data);
    }

    /**
     * Update template vital signs.
     */
    public function updateVitalSigns(array $vitalSignIds): bool
    {
        // Validate that all vital signs belong to the same clinic
        $validIds = CustomVitalSignsConfig::whereIn('id', $vitalSignIds)
                                         ->where('clinic_id', $this->clinic_id)
                                         ->where('is_active', true)
                                         ->pluck('id')
                                         ->toArray();

        return $this->update(['vital_sign_ids' => $validIds]);
    }
}
