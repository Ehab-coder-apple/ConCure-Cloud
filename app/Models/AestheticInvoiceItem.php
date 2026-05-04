<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AestheticInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'treatment_id',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the invoice this item belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(AestheticInvoice::class, 'invoice_id');
    }

    /**
     * Get the treatment associated with this item.
     */
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(AestheticTreatment::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($item) {
            $item->invoice->recalculateSubtotal();
        });

        static::deleted(function ($item) {
            $item->invoice->recalculateSubtotal();
        });
    }
}
