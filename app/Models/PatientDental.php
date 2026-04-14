<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDental extends Model
{
    use HasFactory;

    public const ORAL_HYGIENE_STATUSES = [
        'excellent' => 'Excellent',
        'good' => 'Good',
        'fair' => 'Fair',
        'poor' => 'Poor',
    ];

    public const SMOKING_STATUSES = [
        'never' => 'Never',
        'former' => 'Former',
        'current' => 'Current',
        'unknown' => 'Unknown',
    ];

    protected $table = 'patient_dental';

    protected $fillable = [
        'patient_id',
        'oral_hygiene',
        'smoking_status',
        'bruxism',
        'notes',
    ];

    protected $casts = [
        'bruxism' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function getOralHygieneLabelAttribute(): ?string
    {
        return static::ORAL_HYGIENE_STATUSES[$this->oral_hygiene] ?? $this->oral_hygiene;
    }

    public function getSmokingStatusLabelAttribute(): ?string
    {
        return static::SMOKING_STATUSES[$this->smoking_status] ?? $this->smoking_status;
    }
}