<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomCheckupTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'name',
        'description',
        'medical_condition',
        'specialty',
        'checkup_type',
        'form_config',
        'is_active',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'form_config' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
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
     * Get the custom fields for this template.
     */
    public function customFields()
    {
        return $this->hasMany(\App\Models\CustomCheckupField::class, 'template_id');
    }

    /**
     * Get the patient assignments for this template.
     */
    public function patientAssignments()
    {
        return $this->hasMany(\App\Models\PatientCheckupTemplateAssignment::class, 'template_id');
    }

    /**
     * Get the checkups that used this template.
     */
    public function checkups()
    {
        return $this->hasMany(PatientCheckup::class, 'template_id');
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
     * Scope to get templates by medical condition.
     */
    public function scopeByCondition($query, $condition)
    {
        return $query->where('medical_condition', $condition);
    }

    /**
     * Scope to get templates by specialty.
     */
    public function scopeBySpecialty($query, $specialty)
    {
        return $query->where('specialty', $specialty);
    }

    /**
     * Scope to get templates by checkup type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('checkup_type', $type);
    }

    /**
     * Scope to get default templates.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the form sections from config.
     */
    public function getFormSectionsAttribute(): array
    {
        return $this->form_config['sections'] ?? [];
    }

    /**
     * Get all form fields flattened.
     */
    public function getFormFieldsAttribute(): array
    {
        $fields = [];
        $sections = $this->form_sections;

        foreach ($sections as $sectionKey => $section) {
            if (isset($section['fields'])) {
                foreach ($section['fields'] as $fieldKey => $field) {
                    $fields[$fieldKey] = array_merge($field, [
                        'section' => $sectionKey,
                        'field_name' => $fieldKey
                    ]);
                }
            }
        }

        return $fields;
    }

    /**
     * Get the number of sections in this template.
     */
    public function getSectionsCountAttribute(): int
    {
        return count($this->form_sections);
    }

    /**
     * Get the number of fields in this template.
     */
    public function getFieldsCountAttribute(): int
    {
        return count($this->form_fields);
    }

    /**
     * Get template usage statistics.
     */
    public function getUsageStatsAttribute(): array
    {
        $assignmentsCount = $this->patientAssignments()->count();
        $activeAssignmentsCount = $this->patientAssignments()->where('is_active', true)->count();
        $checkupsCount = $this->checkups()->count();

        return [
            'total_assignments' => $assignmentsCount,
            'active_assignments' => $activeAssignmentsCount,
            'total_checkups' => $checkupsCount,
            'usage_rate' => $assignmentsCount > 0 ? round(($checkupsCount / $assignmentsCount) * 100, 1) : 0,
        ];
    }

    /**
     * Get available checkup types.
     */
    public static function getCheckupTypes(): array
    {
        return [
            'initial' => 'Initial Consultation',
            'follow_up' => 'Follow-up Visit',
            'emergency' => 'Emergency Visit',
            'specialty' => 'Specialty Consultation',
            'pre_op' => 'Pre-Operative',
            'post_op' => 'Post-Operative',
            'screening' => 'Screening/Preventive',
            'routine' => 'Routine Check-up',
        ];
    }

    /**
     * Get available field types.
     */
    public static function getFieldTypes(): array
    {
        return [
            'text' => 'Text Input',
            'textarea' => 'Text Area',
            'number' => 'Number Input',
            'select' => 'Dropdown Select',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Buttons',
            'date' => 'Date Picker',
            'time' => 'Time Picker',
            'datetime' => 'Date & Time',
            'file' => 'File Upload',
            'email' => 'Email Input',
            'phone' => 'Phone Input',
            'url' => 'URL Input',
        ];
    }

    /**
     * Validate form configuration.
     */
    public function validateFormConfig(): array
    {
        $errors = [];
        $config = $this->form_config;

        if (!isset($config['sections']) || !is_array($config['sections'])) {
            $errors[] = 'Form configuration must have sections';
            return $errors;
        }

        foreach ($config['sections'] as $sectionKey => $section) {
            if (!isset($section['title'])) {
                $errors[] = "Section '{$sectionKey}' must have a title";
            }

            if (!isset($section['fields']) || !is_array($section['fields'])) {
                $errors[] = "Section '{$sectionKey}' must have fields";
                continue;
            }

            foreach ($section['fields'] as $fieldKey => $field) {
                if (!isset($field['type'])) {
                    $errors[] = "Field '{$fieldKey}' must have a type";
                }

                if (!isset($field['label'])) {
                    $errors[] = "Field '{$fieldKey}' must have a label";
                }

                if (isset($field['type']) && in_array($field['type'], ['select', 'radio']) && !isset($field['options'])) {
                    $errors[] = "Field '{$fieldKey}' of type '{$field['type']}' must have options";
                }
            }
        }

        return $errors;
    }

    /**
     * Create template from form builder data.
     */
    public static function createFromFormBuilder(array $data, User $creator): self
    {
        $template = self::create([
            'clinic_id' => $creator->clinic_id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'medical_condition' => $data['medical_condition'] ?? null,
            'specialty' => $data['specialty'] ?? null,
            'checkup_type' => $data['checkup_type'] ?? 'follow_up',
            'form_config' => $data['form_config'],
            'is_active' => true,
            'is_default' => $data['is_default'] ?? false,
            'created_by' => $creator->id,
        ]);

        return $template;
    }

    /**
     * Clone template for customization.
     */
    public function cloneTemplate(string $newName, User $creator): self
    {
        return self::create([
            'clinic_id' => $creator->clinic_id,
            'name' => $newName,
            'description' => "Cloned from: " . $this->name,
            'medical_condition' => $this->medical_condition,
            'specialty' => $this->specialty,
            'checkup_type' => $this->checkup_type,
            'form_config' => $this->form_config,
            'is_active' => true,
            'is_default' => false,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * Get default templates for common conditions.
     */
    public static function getDefaultTemplates(): array
    {
        return [
            'pre_surgery' => [
                'name' => 'Pre-Surgery Assessment',
                'description' => 'Comprehensive pre-operative evaluation',
                'medical_condition' => 'Pre-Surgery',
                'specialty' => 'Surgery',
                'checkup_type' => 'pre_op',
            ],
            'diabetes' => [
                'name' => 'Diabetes Follow-up',
                'description' => 'Diabetes management and monitoring',
                'medical_condition' => 'Diabetes',
                'specialty' => 'Endocrinology',
                'checkup_type' => 'follow_up',
            ],
            'cardiac' => [
                'name' => 'Cardiac Assessment',
                'description' => 'Comprehensive cardiac evaluation',
                'medical_condition' => 'Cardiac',
                'specialty' => 'Cardiology',
                'checkup_type' => 'follow_up',
            ],
            'mental_health' => [
                'name' => 'Mental Health Assessment',
                'description' => 'Mental health evaluation and screening',
                'medical_condition' => 'Mental Health',
                'specialty' => 'Psychiatry',
                'checkup_type' => 'follow_up',
            ],
        ];
    }
}
