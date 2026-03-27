<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'is_system',
        'clinic_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

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

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeTenant($query, ?int $clinicId = null)
    {
        return $query->where('is_system', false)->when($clinicId, fn($q) => $q->where('clinic_id', $clinicId));
    }

    /**
     * Check if this drug can be deleted by the given user.
     */
    public function canBeDeletedBy($user): bool
    {
        // System drugs can only be deleted by superadmin
        if ($this->is_system) {
            return $user->isSuperAdmin();
        }

        // Tenant drugs can be deleted by superadmin or users from the same clinic
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->clinic_id === $user->clinic_id;
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

