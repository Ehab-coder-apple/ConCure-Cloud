<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'normal_range_min',
        'normal_range_max',
        'unit',
        'is_frequent',
        'clinic_id',
        'is_active',
    ];

    protected $casts = [
        'normal_range_min' => 'decimal:2',
        'normal_range_max' => 'decimal:2',
        'is_frequent' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Lab test categories
     */
    const CATEGORIES = [
        'blood' => 'Blood',
        'urine' => 'Urine',
        'stool' => 'Stool',
        'imaging' => 'Imaging',
        'biopsy' => 'Biopsy',
        'culture' => 'Culture',
        'genetic' => 'Genetic',
        'hormone' => 'Hormone',
        'cardiac' => 'Cardiac',
        'other' => 'Other',
    ];

    /**
     * Get the clinic that owns the lab test.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the lab request tests for this lab test.
     */
    public function labRequestTests(): HasMany
    {
        return $this->hasMany(LabRequestTest::class);
    }

    /**
     * Get the category display name.
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get the normal range display.
     */
    public function getNormalRangeDisplayAttribute(): string
    {
        if ($this->normal_range_min !== null && $this->normal_range_max !== null) {
            $range = $this->normal_range_min . ' - ' . $this->normal_range_max;
            if ($this->unit) {
                $range .= ' ' . $this->unit;
            }
            return $range;
        } elseif ($this->normal_range_max !== null) {
            $range = '< ' . $this->normal_range_max;
            if ($this->unit) {
                $range .= ' ' . $this->unit;
            }
            return $range;
        } elseif ($this->normal_range_min !== null) {
            $range = '> ' . $this->normal_range_min;
            if ($this->unit) {
                $range .= ' ' . $this->unit;
            }
            return $range;
        }
        
        return 'Not specified';
    }

    /**
     * Get the full test name with code.
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->name;
        if ($this->code) {
            $name .= ' (' . $this->code . ')';
        }
        return $name;
    }

    /**
     * Check if a value is within normal range.
     */
    public function isValueNormal(float $value): bool
    {
        if ($this->normal_range_min !== null && $value < $this->normal_range_min) {
            return false;
        }
        
        if ($this->normal_range_max !== null && $value > $this->normal_range_max) {
            return false;
        }
        
        return true;
    }

    /**
     * Get value status (normal, low, high).
     */
    public function getValueStatus(float $value): string
    {
        if ($this->normal_range_min !== null && $value < $this->normal_range_min) {
            return 'low';
        }
        
        if ($this->normal_range_max !== null && $value > $this->normal_range_max) {
            return 'high';
        }
        
        return 'normal';
    }

    /**
     * Scope to filter active lab tests.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter frequent lab tests.
     */
    public function scopeFrequent($query)
    {
        return $query->where('is_frequent', true);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to search lab tests.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
