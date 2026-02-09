<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiologyTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'body_part',
        'preparation_instructions',
        'contrast_requirements',
        'estimated_duration_minutes',
        'estimated_cost',
        'requires_contrast',
        'requires_fasting',
        'is_frequent',
        'clinic_id',
        'is_active',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'requires_contrast' => 'boolean',
        'requires_fasting' => 'boolean',
        'is_frequent' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Radiology test categories
     */
    const CATEGORIES = [
        'x_ray' => 'X-Ray',
        'ct_scan' => 'CT Scan',
        'mri' => 'MRI',
        'ultrasound' => 'Ultrasound',
        'mammography' => 'Mammography',
        'nuclear_medicine' => 'Nuclear Medicine',
        'fluoroscopy' => 'Fluoroscopy',
        'angiography' => 'Angiography',
        'pet_scan' => 'PET Scan',
        'bone_scan' => 'Bone Scan',
        'other' => 'Other',
    ];

    /**
     * Body parts
     */
    const BODY_PARTS = [
        'head' => 'Head',
        'neck' => 'Neck',
        'chest' => 'Chest',
        'abdomen' => 'Abdomen',
        'pelvis' => 'Pelvis',
        'spine' => 'Spine',
        'upper_extremity' => 'Upper Extremity',
        'lower_extremity' => 'Lower Extremity',
        'whole_body' => 'Whole Body',
        'other' => 'Other',
    ];

    /**
     * Get the clinic that owns the radiology test.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the radiology request tests for this radiology test.
     */
    public function radiologyRequestTests(): HasMany
    {
        return $this->hasMany(RadiologyRequestTest::class);
    }

    /**
     * Get the category display name.
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get the body part display name.
     */
    public function getBodyPartDisplayAttribute(): string
    {
        return self::BODY_PARTS[$this->body_part] ?? $this->body_part;
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
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->estimated_duration_minutes) {
            return 'Not specified';
        }

        $hours = intval($this->estimated_duration_minutes / 60);
        $minutes = $this->estimated_duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
        }

        return $minutes . ' minutes';
    }

    /**
     * Get formatted cost.
     */
    public function getFormattedCostAttribute(): string
    {
        if (!$this->estimated_cost) {
            return 'Not specified';
        }

        return '$' . number_format($this->estimated_cost, 2);
    }

    /**
     * Get preparation summary.
     */
    public function getPreparationSummaryAttribute(): string
    {
        $summary = [];
        
        if ($this->requires_fasting) {
            $summary[] = 'Fasting required';
        }
        
        if ($this->requires_contrast) {
            $summary[] = 'Contrast may be used';
        }
        
        if ($this->preparation_instructions) {
            $summary[] = 'Special preparation needed';
        }

        return empty($summary) ? 'No special preparation' : implode(', ', $summary);
    }

    /**
     * Scope to filter active radiology tests.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter frequent radiology tests.
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
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by body part.
     */
    public function scopeByBodyPart($query, string $bodyPart)
    {
        return $query->where('body_part', $bodyPart);
    }

    /**
     * Scope to search radiology tests.
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
     * Scope to order by frequency and name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('is_frequent', 'desc')
                    ->orderBy('category')
                    ->orderBy('name');
    }
}
