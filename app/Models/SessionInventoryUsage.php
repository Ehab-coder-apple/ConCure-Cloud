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
        'product_id',
        'quantity_used',
    ];

    protected $casts = [
        'quantity_used' => 'integer',
    ];

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
