<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionInventoryUsage extends Model
{
    use HasFactory;

    protected $table = 'session_inventory_usage';

    protected $fillable = [
        'session_id',
        'tenant_id',
        'product_id',
        'quantity_used',
    ];

    protected $casts = [
        'quantity_used' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (\Illuminate\Database\Eloquent\Builder $query) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            if ($tenantId) {
                $query->where('session_inventory_usage.tenant_id', $tenantId);
            } else {
                $query->whereRaw('1 = 0'); // No tenant context = no data
            }
        });

        static::creating(function ($usage) {
            if (empty($usage->tenant_id) && $usage->session) {
                $usage->tenant_id = $usage->session->tenant_id;
            }
        });
    }

    /**
     * Get the session this usage belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AestheticSession::class, 'session_id');
    }

    /**
     * Get the product used.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(AestheticInventory::class, 'product_id');
    }
}
