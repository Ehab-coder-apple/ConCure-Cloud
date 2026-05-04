<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AestheticPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'treatment_id',
        'total_sessions',
        'price',
        'discount',
        'expiry_date',
    ];

    protected $casts = [
        'total_sessions' => 'integer',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'expiry_date' => 'date',
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

        static::creating(function ($package) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            if ($tenantId) {
                $package->tenant_id = $tenantId;
            }
        });
    }

    /**
     * Get the primary treatment this package belongs to (backward compat).
     */
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(AestheticTreatment::class);
    }

    /**
     * Get all treatments in this package (multi-treatment support).
     */
    public function treatments(): BelongsToMany
    {
        return $this->belongsToMany(AestheticTreatment::class, 'aesthetic_package_treatment', 'package_id', 'treatment_id');
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
     * Scope to filter active (not expired) packages.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now()->toDateString());
        });
    }

    /**
     * Scope to filter expired packages.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<', now()->toDateString());
    }

    /**
     * Get final price after discount.
     */
    public function getFinalPriceAttribute(): float
    {
        return max(0, (float) $this->price - (float) ($this->discount ?? 0));
    }

    /**
     * Check if package is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }
}
