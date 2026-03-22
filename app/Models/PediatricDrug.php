<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PediatricDrug extends Model
{
    use HasFactory;

    protected $fillable = [
        'generic_name',
        'brand_name',
        'category',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function forms(): HasMany
    {
        return $this->hasMany(PediatricDrugForm::class, 'drug_id');
    }

    public function dosageRules(): HasMany
    {
        return $this->hasMany(PediatricDosageRule::class, 'drug_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(PediatricPrescription::class, 'drug_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the best matching dosage rule for a given age and weight.
     */
    public function findDosageRule(?int $ageMonths = null, ?float $weightKg = null): ?PediatricDosageRule
    {
        $query = $this->dosageRules();

        if ($ageMonths !== null) {
            $query->where(function ($q) use ($ageMonths) {
                $q->where(function ($inner) use ($ageMonths) {
                    $inner->whereNull('min_age_months')->orWhere('min_age_months', '<=', $ageMonths);
                })->where(function ($inner) use ($ageMonths) {
                    $inner->whereNull('max_age_months')->orWhere('max_age_months', '>=', $ageMonths);
                });
            });
        }

        if ($weightKg !== null) {
            $query->where(function ($q) use ($weightKg) {
                $q->where(function ($inner) use ($weightKg) {
                    $inner->whereNull('min_weight_kg')->orWhere('min_weight_kg', '<=', $weightKg);
                })->where(function ($inner) use ($weightKg) {
                    $inner->whereNull('max_weight_kg')->orWhere('max_weight_kg', '>=', $weightKg);
                });
            });
        }

        return $query->first();
    }
}

