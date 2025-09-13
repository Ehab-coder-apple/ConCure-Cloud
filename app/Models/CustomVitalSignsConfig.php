<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomVitalSignsConfig extends Model
{
    use HasFactory;

    protected $table = 'custom_vital_signs_config';

    protected $fillable = [
        'clinic_id',
        'name',
        'unit',
        'type',
        'options',
        'min_value',
        'max_value',
        'normal_range',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the clinic that owns this custom vital sign configuration.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Scope to get active custom vital signs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get custom vital signs for a specific clinic.
     */
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to get ordered custom vital signs.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the display name with unit.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->unit ? ' (' . $this->unit . ')' : '');
    }

    /**
     * Check if a value is within normal range.
     */
    public function isValueNormal($value): bool
    {
        if ($this->type === 'number' && $this->min_value && $this->max_value) {
            $numericValue = (float) $value;
            return $numericValue >= $this->min_value && $numericValue <= $this->max_value;
        }

        if ($this->type === 'select' && $this->normal_range) {
            return $value === $this->normal_range;
        }

        return true; // Default to normal if we can't determine
    }

    /**
     * Get the status class for a value (for UI styling).
     */
    public function getValueStatusClass($value): string
    {
        if ($this->isValueNormal($value)) {
            return 'text-success';
        }

        return 'text-warning';
    }

    /**
     * Validate a value against this vital sign's constraints.
     */
    public function validateValue($value): array
    {
        $errors = [];

        if ($this->type === 'number') {
            if (!is_numeric($value)) {
                $errors[] = "Value must be a number";
            } else {
                $numericValue = (float) $value;
                
                if ($this->min_value && $numericValue < $this->min_value) {
                    $errors[] = "Value must be at least {$this->min_value}";
                }
                
                if ($this->max_value && $numericValue > $this->max_value) {
                    $errors[] = "Value must not exceed {$this->max_value}";
                }
            }
        }

        if ($this->type === 'select' && $this->options) {
            $validOptions = array_keys($this->options);
            if (!in_array($value, $validOptions)) {
                $errors[] = "Invalid option selected";
            }
        }

        return $errors;
    }

    /**
     * Get formatted value for display.
     */
    public function formatValue($value): string
    {
        if ($this->type === 'select' && $this->options && isset($this->options[$value])) {
            return $this->options[$value];
        }

        return $value . ($this->unit ? ' ' . $this->unit : '');
    }

    /**
     * Get default custom vital signs for a clinic.
     */
    public static function getDefaultSigns(): array
    {
        return [
            [
                'name' => 'Oxygen Saturation',
                'unit' => '%',
                'type' => 'number',
                'min_value' => 70,
                'max_value' => 100,
                'normal_range' => '95-100%',
                'sort_order' => 1,
            ],
            [
                'name' => 'Peak Flow',
                'unit' => 'L/min',
                'type' => 'number',
                'min_value' => 50,
                'max_value' => 800,
                'normal_range' => '400-700 L/min',
                'sort_order' => 2,
            ],
            [
                'name' => 'Pain Level',
                'unit' => '/10',
                'type' => 'select',
                'options' => [
                    '0' => 'No Pain (0)',
                    '1' => 'Mild (1)',
                    '2' => 'Mild (2)',
                    '3' => 'Moderate (3)',
                    '4' => 'Moderate (4)',
                    '5' => 'Moderate (5)',
                    '6' => 'Severe (6)',
                    '7' => 'Severe (7)',
                    '8' => 'Very Severe (8)',
                    '9' => 'Very Severe (9)',
                    '10' => 'Worst Possible (10)',
                ],
                'min_value' => 0,
                'max_value' => 10,
                'normal_range' => '0-2/10',
                'sort_order' => 3,
            ],
            [
                'name' => 'Mobility Status',
                'unit' => '',
                'type' => 'select',
                'options' => [
                    'independent' => 'Independent',
                    'assisted' => 'Assisted',
                    'wheelchair' => 'Wheelchair',
                    'bedbound' => 'Bedbound',
                ],
                'normal_range' => 'Independent',
                'sort_order' => 4,
            ],
            [
                'name' => 'Mental Status',
                'unit' => '',
                'type' => 'select',
                'options' => [
                    'alert' => 'Alert & Oriented',
                    'confused' => 'Confused',
                    'drowsy' => 'Drowsy',
                    'unconscious' => 'Unconscious',
                ],
                'normal_range' => 'Alert & Oriented',
                'sort_order' => 5,
            ],
        ];
    }
}
