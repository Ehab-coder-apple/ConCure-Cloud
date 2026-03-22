<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'created_by',
        'target_weight',
        'target_fat_percentage',
        'target_muscle_percentage',
        'target_bmi',
        'target_waist_cm',
        'target_hip_cm',
        'target_visceral_fat',
        'target_body_water_percentage',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'target_weight' => 'decimal:2',
        'target_fat_percentage' => 'decimal:2',
        'target_muscle_percentage' => 'decimal:2',
        'target_bmi' => 'decimal:2',
        'target_waist_cm' => 'decimal:2',
        'target_hip_cm' => 'decimal:2',
        'target_visceral_fat' => 'decimal:2',
        'target_body_water_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

