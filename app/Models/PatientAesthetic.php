<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAesthetic extends Model
{
    use HasFactory;

    protected $table = 'patient_aesthetic';

    public const SKIN_TYPES = [
        'normal' => 'Normal',
        'oily' => 'Oily',
        'dry' => 'Dry',
        'combination' => 'Combination',
        'sensitive' => 'Sensitive',
    ];

    public const SKIN_CONCERNS = [
        'acne' => 'Acne / Breakouts',
        'aging' => 'Aging / Fine Lines',
        'wrinkles' => 'Deep Wrinkles',
        'pigmentation' => 'Pigmentation / Dark Spots',
        'scars' => 'Scars / Acne Scars',
        'redness' => 'Redness / Rosacea',
        'dullness' => 'Dullness / Uneven Tone',
        'pores' => 'Enlarged Pores',
        'dryness' => 'Dryness / Dehydration',
        'sagging' => 'Skin Sagging',
        'under_eye' => 'Under-eye Bags / Dark Circles',
        'hair_loss' => 'Hair Loss',
        'cellulite' => 'Cellulite',
        'stretch_marks' => 'Stretch Marks',
        'other' => 'Other',
    ];

    public const SUN_EXPOSURE = [
        'low' => 'Low (mostly indoors)',
        'moderate' => 'Moderate (some outdoor time)',
        'high' => 'High (frequent outdoor/sun exposure)',
    ];

    protected $fillable = [
        'patient_id',
        'skin_type',
        'skin_concerns',
        'allergies',
        'previous_treatments',
        'current_skincare_routine',
        'desired_outcomes',
        'sun_exposure',
        'is_pregnant_or_breastfeeding',
        'photosensitivity',
        'medical_conditions',
        'notes',
    ];

    protected $casts = [
        'skin_concerns' => 'array',
        'is_pregnant_or_breastfeeding' => 'boolean',
        'photosensitivity' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function getSkinTypeLabelAttribute(): ?string
    {
        return static::SKIN_TYPES[$this->skin_type] ?? $this->skin_type;
    }

    public function getSunExposureLabelAttribute(): ?string
    {
        return static::SUN_EXPOSURE[$this->sun_exposure] ?? $this->sun_exposure;
    }

    public function getSkinConcernsLabelsAttribute(): array
    {
        if (empty($this->skin_concerns)) {
            return [];
        }
        return collect($this->skin_concerns)
            ->map(fn ($key) => static::SKIN_CONCERNS[$key] ?? $key)
            ->values()
            ->all();
    }
}
