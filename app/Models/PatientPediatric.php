<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientPediatric extends Model
{
    use HasFactory;

    public const FEEDING_TYPES = [
        'breastfeeding' => 'Breastfeeding',
        'formula' => 'Formula',
        'mixed' => 'Mixed Feeding',
        'solids' => 'Solids / Complementary Feeding',
        'unknown' => 'Unknown',
    ];

    public const VACCINATION_STATUSES = [
        'up_to_date' => 'Up to Date',
        'delayed' => 'Delayed',
        'incomplete' => 'Incomplete',
        'not_started' => 'Not Started',
        'unknown' => 'Unknown',
    ];

    protected $table = 'patient_pediatric';

    protected $fillable = [
        'patient_id',
        'birth_weight',
        'gestational_age',
        'feeding_type',
        'vaccination_status',
        'notes',
    ];

    protected $casts = [
        'birth_weight' => 'decimal:2',
        'gestational_age' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function getFeedingTypeLabelAttribute(): ?string
    {
        return static::FEEDING_TYPES[$this->feeding_type] ?? $this->feeding_type;
    }

    public function getVaccinationStatusLabelAttribute(): ?string
    {
        return static::VACCINATION_STATUSES[$this->vaccination_status] ?? $this->vaccination_status;
    }

    public function getIsLowBirthWeightAttribute(): bool
    {
        return $this->birth_weight !== null && (float) $this->birth_weight < 2500;
    }

    public function getIsPretermAttribute(): bool
    {
        return $this->gestational_age !== null && (int) $this->gestational_age < 37;
    }

    public function getGrowthStatusLabelAttribute(): string
    {
        if ($this->is_low_birth_weight && $this->is_preterm) {
            return 'LBW • Preterm';
        }

        if ($this->is_low_birth_weight) {
            return 'LBW';
        }

        if ($this->is_preterm) {
            return 'Preterm';
        }

        return 'Standard';
    }

    public function getClassificationLabelsAttribute(): array
    {
        return array_values(array_filter([
            $this->is_low_birth_weight ? 'LBW' : null,
            $this->is_preterm ? 'Preterm' : null,
        ]));
    }
}