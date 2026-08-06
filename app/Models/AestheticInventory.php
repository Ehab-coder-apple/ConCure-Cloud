<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AestheticInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'aesthetic_inventory';

    protected $fillable = [
        'tenant_id',
        'product_name',
        'type',
        'quantity',
        'purchased_quantity',
        'bonus_quantity',
        'low_stock_threshold',
        'expiry_date',
        'purchase_price',
        'selling_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchased_quantity' => 'integer',
        'bonus_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    const TYPES = [
        'consumable' => 'Consumable',
        'equipment' => 'Equipment',
        'medication' => 'Medication',
        'other' => 'Other',
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
            } else {
                $query->whereRaw('1 = 0'); // No tenant context = no data
            }
        });

        static::creating(function ($item) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            if ($tenantId && empty($item->tenant_id)) {
                $item->tenant_id = $tenantId;
            }
        });
    }

    /**
     * Get the session usages for this product.
     */
    public function sessionUsages(): HasMany
    {
        return $this->hasMany(SessionInventoryUsage::class, 'product_id');
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
     * Scope to filter low stock items.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    /**
     * Scope to filter expired items.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<', now()->toDateString());
    }

    /**
     * Scope to filter items near expiry (within 30 days).
     */
    public function scopeNearExpiry(Builder $query): Builder
    {
        return $query->whereNotNull('expiry_date')
                     ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()]);
    }

    /**
     * Check if stock is low.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    /**
     * Check if item is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Check if item is near expiry.
     */
    public function getIsNearExpiryAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isBetween(now(), now()->addDays(30));
    }

    /**
     * Deduct quantity from stock.
     *
     * Deducts from purchased stock first, then falls back to bonus stock
     * once purchased stock is exhausted, while keeping the aggregate
     * `quantity` column in sync.
     */
    public function deductStock(int $amount): bool
    {
        if ($this->quantity < $amount) {
            return false;
        }

        $fromPurchased = min($this->purchased_quantity, $amount);
        $fromBonus = $amount - $fromPurchased;

        if ($fromPurchased > 0) {
            $this->decrement('purchased_quantity', $fromPurchased);
        }
        if ($fromBonus > 0) {
            $this->decrement('bonus_quantity', $fromBonus);
        }
        $this->decrement('quantity', $amount);

        return true;
    }

    /**
     * Add quantity to stock.
     *
     * By default the added quantity is treated as "purchased" stock unless
     * a different stock type is specified.
     */
    public function addStock(int $amount, string $stockType = 'purchased'): void
    {
        if ($stockType === 'bonus') {
            $this->increment('bonus_quantity', $amount);
        } else {
            $this->increment('purchased_quantity', $amount);
        }
        $this->increment('quantity', $amount);
    }
}
