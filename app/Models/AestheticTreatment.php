<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class AestheticTreatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'default_price',
        'session_required',
        'sessions_count',
        'description',
        'is_active',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'session_required' => 'boolean',
        'sessions_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Available treatment categories.
     */
    const CATEGORIES = [
        'laser' => 'Laser',
        'injectables' => 'Injectables',
        'skincare' => 'Skincare',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }
        });

        static::creating(function ($treatment) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            if ($tenantId) {
                $treatment->tenant_id = $tenantId;
            }
        });
    }

    /**
     * Scope to filter by tenant.
     */
    public function scopeByTenant(Builder $query, ?string $tenantId): Builder
    {
        if ($tenantId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to filter active treatments.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to search treatments.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get the category display name.
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get the category badge color.
     */
    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'laser' => 'danger',
            'injectables' => 'info',
            'skincare' => 'success',
            default => 'secondary',
        };
    }
}
