<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalOverview extends Model
{
    use HasFactory;

    public const FLAG_LABELS = [
        'pregnant' => 'Pregnant',
        'diabetic' => 'Diabetic',
        'hypertensive' => 'Hypertensive',
    ];

    protected $fillable = [
        'patient_id',
        'allergies',
        'chronic_diseases',
        'surgeries',
        'medical_history',
        'current_medications_summary',
        'flags',
    ];

    protected $casts = [
        'flags' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function hasFlag(string $flag): bool
    {
        return (bool) data_get($this->flags ?? [], $flag, false);
    }

    public function activeFlagLabels(): array
    {
        return collect(static::FLAG_LABELS)
            ->filter(fn ($label, $flag) => $this->hasFlag($flag))
            ->values()
            ->all();
    }
}