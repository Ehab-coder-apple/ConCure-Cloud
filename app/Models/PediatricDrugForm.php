<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PediatricDrugForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'drug_id',
        'form',
        'concentration',
        'concentration_mg',
        'concentration_per_ml',
        'notes',
    ];

    protected $casts = [
        'concentration_mg' => 'decimal:2',
        'concentration_per_ml' => 'decimal:2',
    ];

    public function drug(): BelongsTo
    {
        return $this->belongsTo(PediatricDrug::class, 'drug_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(PediatricPrescription::class, 'form_id');
    }

    /**
     * Convert mg dose to ml for liquid forms.
     */
    public function convertMgToMl(float $doseMg): ?float
    {
        if (!$this->concentration_per_ml || $this->concentration_mg <= 0) {
            return null;
        }

        // concentration_mg per concentration_per_ml ml
        // e.g. 120mg per 5ml → dose_ml = (doseMg * 5) / 120
        return round(($doseMg * $this->concentration_per_ml) / $this->concentration_mg, 2);
    }

    /**
     * Check if this is a liquid form (has ml conversion).
     */
    public function getIsLiquidAttribute(): bool
    {
        return $this->concentration_per_ml !== null && $this->concentration_per_ml > 0;
    }

    /**
     * Get display label.
     */
    public function getDisplayLabelAttribute(): string
    {
        return ucfirst($this->form) . ' (' . $this->concentration . ')';
    }
}

