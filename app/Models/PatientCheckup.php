<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCheckup extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'weight',
        'height',
        'bmi',
        'blood_pressure',
        'heart_rate',
        'temperature',
        'respiratory_rate',
        'blood_sugar',
        'custom_vital_signs',
        'custom_fields',
        'template_id',
        'symptoms',
        'notes',
        'recommendations',
        'recorded_by',
        'checkup_date',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
        'temperature' => 'decimal:1',
        'blood_sugar' => 'decimal:2',
        'custom_vital_signs' => 'array',
        'custom_fields' => 'array',
        'checkup_date' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($checkup) {
            // Calculate BMI if height and weight are provided
            if ($checkup->height && $checkup->weight) {
                $checkup->bmi = Patient::calculateBMI($checkup->weight, $checkup->height);
            }
            
            // Set checkup date if not provided
            if (!$checkup->checkup_date) {
                $checkup->checkup_date = now();
            }
        });

        static::updating(function ($checkup) {
            // Recalculate BMI if height or weight changed
            if ($checkup->isDirty(['height', 'weight']) && $checkup->height && $checkup->weight) {
                $checkup->bmi = Patient::calculateBMI($checkup->weight, $checkup->height);
            }
        });
    }

    /**
     * Get the patient that owns the checkup.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who recorded this checkup.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get blood pressure status.
     */
    public function getBloodPressureStatusAttribute(): string
    {
        if (!$this->blood_pressure) {
            return 'Unknown';
        }

        // Parse blood pressure (e.g., "120/80")
        $parts = explode('/', $this->blood_pressure);
        if (count($parts) !== 2) {
            return 'Invalid';
        }

        $systolic = (int) $parts[0];
        $diastolic = (int) $parts[1];

        if ($systolic < 90 || $diastolic < 60) {
            return 'Low';
        } elseif ($systolic < 120 && $diastolic < 80) {
            return 'Normal';
        } elseif ($systolic < 130 && $diastolic < 80) {
            return 'Elevated';
        } elseif ($systolic < 140 || $diastolic < 90) {
            return 'High Stage 1';
        } elseif ($systolic < 180 || $diastolic < 120) {
            return 'High Stage 2';
        } else {
            return 'Hypertensive Crisis';
        }
    }

    /**
     * Get heart rate status.
     */
    public function getHeartRateStatusAttribute(): string
    {
        if (!$this->heart_rate) {
            return 'Unknown';
        }

        if ($this->heart_rate < 60) {
            return 'Low (Bradycardia)';
        } elseif ($this->heart_rate <= 100) {
            return 'Normal';
        } else {
            return 'High (Tachycardia)';
        }
    }

    /**
     * Get temperature status.
     */
    public function getTemperatureStatusAttribute(): string
    {
        if (!$this->temperature) {
            return 'Unknown';
        }

        if ($this->temperature < 36.1) {
            return 'Low';
        } elseif ($this->temperature <= 37.2) {
            return 'Normal';
        } elseif ($this->temperature <= 38.0) {
            return 'Mild Fever';
        } elseif ($this->temperature <= 39.0) {
            return 'Moderate Fever';
        } else {
            return 'High Fever';
        }
    }

    /**
     * Get BMI category.
     */
    public function getBmiCategoryAttribute(): string
    {
        if (!$this->bmi) {
            return 'Unknown';
        }

        if ($this->bmi < 18.5) {
            return 'Underweight';
        } elseif ($this->bmi < 25) {
            return 'Normal weight';
        } elseif ($this->bmi < 30) {
            return 'Overweight';
        } else {
            return 'Obese';
        }
    }

    /**
     * Check if any vital signs are abnormal.
     */
    public function hasAbnormalVitals(): bool
    {
        $abnormalStatuses = [
            'Low', 'High', 'Elevated', 'High Stage 1', 'High Stage 2', 
            'Hypertensive Crisis', 'Low (Bradycardia)', 'High (Tachycardia)',
            'Mild Fever', 'Moderate Fever', 'High Fever'
        ];

        return in_array($this->blood_pressure_status, $abnormalStatuses) ||
               in_array($this->heart_rate_status, $abnormalStatuses) ||
               in_array($this->temperature_status, $abnormalStatuses);
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('checkup_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter recent checkups.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('checkup_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by checkup date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('checkup_date', 'desc');
    }

    /**
     * Get custom vital signs with their configurations.
     */
    public function getCustomVitalSignsWithConfigAttribute(): array
    {
        if (!$this->custom_vital_signs || !$this->patient) {
            return [];
        }

        $configs = \App\Models\CustomVitalSignsConfig::forClinic($this->patient->clinic_id)
                                        ->active()
                                        ->ordered()
                                        ->get()
                                        ->keyBy('id');

        $result = [];
        foreach ($this->custom_vital_signs as $configId => $value) {
            if (isset($configs[$configId]) && $value !== null && $value !== '') {
                $config = $configs[$configId];
                $result[] = [
                    'config' => $config,
                    'value' => $value,
                    'formatted_value' => $config->formatValue($value),
                    'is_normal' => $config->isValueNormal($value),
                    'status_class' => $config->getValueStatusClass($value),
                ];
            }
        }

        return $result;
    }

    /**
     * Set custom vital sign value.
     */
    public function setCustomVitalSign(int $configId, $value): void
    {
        $customSigns = $this->custom_vital_signs ?? [];
        $customSigns[$configId] = $value;
        $this->custom_vital_signs = $customSigns;
    }

    /**
     * Get custom vital sign value.
     */
    public function getCustomVitalSign(int $configId)
    {
        return $this->custom_vital_signs[$configId] ?? null;
    }

    /**
     * Check if checkup has any custom vital signs.
     */
    public function hasCustomVitalSigns(): bool
    {
        return !empty($this->custom_vital_signs);
    }

    /**
     * Get the custom checkup template used for this checkup.
     */
    public function template()
    {
        return $this->belongsTo(\App\Models\CustomCheckupTemplate::class, 'template_id');
    }

    /**
     * Get custom field value by field name.
     */
    public function getCustomFieldValue(string $fieldName)
    {
        return $this->custom_fields[$fieldName] ?? null;
    }

    /**
     * Set custom field value.
     */
    public function setCustomFieldValue(string $fieldName, $value): void
    {
        $customFields = $this->custom_fields ?? [];
        $customFields[$fieldName] = $value;
        $this->custom_fields = $customFields;
    }

    /**
     * Get formatted custom fields with their configurations.
     */
    public function getCustomFieldsWithConfigAttribute(): array
    {
        if (!$this->template || empty($this->custom_fields)) {
            return [];
        }

        $formFields = $this->template->form_fields;
        $customFieldsWithConfig = [];

        foreach ($this->custom_fields as $fieldName => $value) {
            if (isset($formFields[$fieldName])) {
                $config = $formFields[$fieldName];
                $customFieldsWithConfig[] = [
                    'field_name' => $fieldName,
                    'config' => $config,
                    'value' => $value,
                    'formatted_value' => $this->formatCustomFieldValue($value, $config),
                    'section' => $config['section'] ?? 'additional',
                ];
            }
        }

        return $customFieldsWithConfig;
    }

    /**
     * Format custom field value for display.
     */
    private function formatCustomFieldValue($value, array $config): string
    {
        if (is_null($value) || $value === '') {
            return '-';
        }

        switch ($config['type']) {
            case 'select':
            case 'radio':
                if (isset($config['options']) && is_array($config['options'])) {
                    return $config['options'][$value] ?? $value;
                }
                return $value;

            case 'checkbox':
                return $value ? 'Yes' : 'No';

            case 'date':
                try {
                    return \Carbon\Carbon::parse($value)->format('M d, Y');
                } catch (\Exception $e) {
                    return $value;
                }

            case 'time':
                try {
                    return \Carbon\Carbon::parse($value)->format('g:i A');
                } catch (\Exception $e) {
                    return $value;
                }

            case 'datetime':
                try {
                    return \Carbon\Carbon::parse($value)->format('M d, Y g:i A');
                } catch (\Exception $e) {
                    return $value;
                }

            case 'number':
                return is_numeric($value) ? number_format($value, 2) : $value;

            default:
                return $value;
        }
    }

    /**
     * Check if checkup has any custom fields.
     */
    public function hasCustomFields(): bool
    {
        return !empty($this->custom_fields);
    }

    /**
     * Get custom fields grouped by section.
     */
    public function getCustomFieldsBySectionAttribute(): array
    {
        $customFieldsWithConfig = $this->custom_fields_with_config;
        $sections = [];

        foreach ($customFieldsWithConfig as $field) {
            $sectionName = $field['section'];
            if (!isset($sections[$sectionName])) {
                $sections[$sectionName] = [];
            }
            $sections[$sectionName][] = $field;
        }

        return $sections;
    }
}
