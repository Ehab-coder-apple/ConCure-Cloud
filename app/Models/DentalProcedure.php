<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'default_cost',
        'currency',
        'estimated_duration_minutes',
        'pre_procedure_instructions',
        'post_procedure_instructions',
        'requires_anesthesia',
        'is_frequent',
        'clinic_id',
        'is_active',
    ];

    protected $casts = [
        'default_cost' => 'decimal:2',
        'requires_anesthesia' => 'boolean',
        'is_frequent' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Procedure categories
     */
    const CATEGORIES = [
        'diagnostic' => 'Diagnostic',
        'preventive' => 'Preventive',
        'restorative' => 'Restorative',
        'endodontics' => 'Endodontics',
        'periodontics' => 'Periodontics',
        'prosthodontics' => 'Prosthodontics',
        'oral_surgery' => 'Oral Surgery',
        'orthodontics' => 'Orthodontics',
        'implants' => 'Implants',
        'cosmetic' => 'Cosmetic',
        'emergency' => 'Emergency',
        'other' => 'Other',
    ];

    /**
     * Get the clinic that owns this procedure.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the category display name.
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, ?int $clinicId)
    {
        if ($clinicId === null) {
            return $query->whereNull('clinic_id');
        }
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to get active procedures.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get frequent procedures.
     */
    public function scopeFrequent($query)
    {
        return $query->where('is_frequent', true);
    }

    /**
     * Scope to search procedures.
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
     * Get global and clinic-specific procedures.
     */
    public static function getAvailableForClinic(?int $clinicId)
    {
        return self::where(function ($query) use ($clinicId) {
            $query->whereNull('clinic_id') // Global procedures
                  ->orWhere('clinic_id', $clinicId); // Clinic-specific procedures
        })
        ->where('is_active', true)
        ->orderBy('is_frequent', 'desc')
        ->orderBy('name');
    }
}

