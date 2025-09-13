<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyRequestTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'radiology_request_id',
        'radiology_test_id',
        'test_name',
        'instructions',
        'clinical_indication',
        'with_contrast',
        'urgent',
        'special_requirements',
    ];

    protected $casts = [
        'with_contrast' => 'boolean',
        'urgent' => 'boolean',
    ];

    /**
     * Get the radiology request that owns this test.
     */
    public function radiologyRequest(): BelongsTo
    {
        return $this->belongsTo(RadiologyRequest::class);
    }

    /**
     * Get the radiology test (if selected from database).
     */
    public function radiologyTest(): BelongsTo
    {
        return $this->belongsTo(RadiologyTest::class);
    }

    /**
     * Get the test name (from database or custom).
     */
    public function getTestNameDisplayAttribute(): string
    {
        return $this->radiologyTest ? $this->radiologyTest->full_name : $this->test_name;
    }

    /**
     * Get the test description.
     */
    public function getTestDescriptionAttribute(): string
    {
        return $this->radiologyTest ? $this->radiologyTest->description : '';
    }

    /**
     * Get the test category.
     */
    public function getTestCategoryAttribute(): string
    {
        return $this->radiologyTest ? $this->radiologyTest->category_display : 'Custom';
    }

    /**
     * Get the body part.
     */
    public function getBodyPartAttribute(): string
    {
        return $this->radiologyTest ? $this->radiologyTest->body_part_display : 'Not specified';
    }

    /**
     * Get preparation instructions.
     */
    public function getPreparationInstructionsAttribute(): string
    {
        return $this->radiologyTest ? $this->radiologyTest->preparation_instructions : 'No special preparation';
    }

    /**
     * Get estimated duration.
     */
    public function getEstimatedDurationAttribute(): string
    {
        return $this->radiologyTest ? $this->radiologyTest->formatted_duration : 'Not specified';
    }

    /**
     * Get contrast requirements display.
     */
    public function getContrastDisplayAttribute(): string
    {
        if ($this->with_contrast) {
            return 'With Contrast';
        }
        
        if ($this->radiologyTest && $this->radiologyTest->requires_contrast) {
            return 'Contrast may be required';
        }
        
        return 'No Contrast';
    }

    /**
     * Get urgency display.
     */
    public function getUrgencyDisplayAttribute(): string
    {
        return $this->urgent ? 'Urgent' : 'Normal';
    }

    /**
     * Get urgency badge class.
     */
    public function getUrgencyBadgeClassAttribute(): string
    {
        return $this->urgent ? 'badge bg-danger' : 'badge bg-secondary';
    }

    /**
     * Get full test summary.
     */
    public function getTestSummaryAttribute(): string
    {
        $summary = $this->test_name_display;
        
        if ($this->with_contrast) {
            $summary .= ' (with contrast)';
        }
        
        if ($this->urgent) {
            $summary .= ' - URGENT';
        }
        
        return $summary;
    }

    /**
     * Scope to filter by radiology request.
     */
    public function scopeByRadiologyRequest($query, int $radiologyRequestId)
    {
        return $query->where('radiology_request_id', $radiologyRequestId);
    }

    /**
     * Scope to filter by radiology test.
     */
    public function scopeByRadiologyTest($query, int $radiologyTestId)
    {
        return $query->where('radiology_test_id', $radiologyTestId);
    }

    /**
     * Scope to filter urgent tests.
     */
    public function scopeUrgent($query)
    {
        return $query->where('urgent', true);
    }

    /**
     * Scope to filter tests with contrast.
     */
    public function scopeWithContrast($query)
    {
        return $query->where('with_contrast', true);
    }
}
