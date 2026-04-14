<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientNutrition extends Model
{
    use HasFactory;

    protected $table = 'patient_nutrition';

    protected $fillable = [
        'patient_id',
        'height',
        'weight',
        'bmi',
        'diet_type',
        'goals',
        'notes',
    ];

    protected $casts = [
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'bmi' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (PatientNutrition $profile): void {
            if ($profile->height && $profile->weight) {
                $profile->bmi = Patient::calculateBMI((float) $profile->weight, (float) $profile->height);
                return;
            }

            $profile->bmi = null;
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}