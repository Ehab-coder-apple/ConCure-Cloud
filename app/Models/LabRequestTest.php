<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabRequestTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_request_id',
        'lab_test_id',
        'test_name',
        'instructions',
    ];

    /**
     * Get the lab request that owns this test.
     */
    public function labRequest(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class);
    }

    /**
     * Get the lab test (if selected from database).
     */
    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    /**
     * Get the test name (from database or custom).
     */
    public function getTestNameDisplayAttribute(): string
    {
        return $this->labTest ? $this->labTest->full_name : $this->test_name;
    }

    /**
     * Get the test description.
     */
    public function getTestDescriptionAttribute(): string
    {
        return $this->labTest ? $this->labTest->description : '';
    }

    /**
     * Get the test category.
     */
    public function getTestCategoryAttribute(): string
    {
        return $this->labTest ? $this->labTest->category_display : 'Custom';
    }

    /**
     * Get the normal range.
     */
    public function getNormalRangeAttribute(): string
    {
        return $this->labTest ? $this->labTest->normal_range_display : 'Not specified';
    }

    /**
     * Scope to filter by lab request.
     */
    public function scopeByLabRequest($query, int $labRequestId)
    {
        return $query->where('lab_request_id', $labRequestId);
    }

    /**
     * Scope to filter by lab test.
     */
    public function scopeByLabTest($query, int $labTestId)
    {
        return $query->where('lab_test_id', $labTestId);
    }
}
