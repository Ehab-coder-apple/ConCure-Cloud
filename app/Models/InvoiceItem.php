<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'item_type',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Item types
     */
    const ITEM_TYPES = [
        'consultation' => 'Consultation',
        'procedure' => 'Procedure',
        'medication' => 'Medication',
        'lab_test' => 'Lab Test',
        'other' => 'Other',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Calculate total price
            $item->total_price = $item->quantity * $item->unit_price;
        });

        static::updating(function ($item) {
            // Recalculate total price if quantity or unit price changed
            if ($item->isDirty(['quantity', 'unit_price'])) {
                $item->total_price = $item->quantity * $item->unit_price;
            }
        });

        static::saved(function ($item) {
            // Update invoice subtotal
            $item->updateInvoiceSubtotal();
        });

        static::deleted(function ($item) {
            // Update invoice subtotal
            $item->updateInvoiceSubtotal();
        });
    }

    /**
     * Get the invoice that owns this item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the item type display name.
     */
    public function getItemTypeDisplayAttribute(): string
    {
        return self::ITEM_TYPES[$this->item_type] ?? $this->item_type;
    }

    /**
     * Update the invoice subtotal.
     */
    private function updateInvoiceSubtotal(): void
    {
        if ($this->invoice) {
            $subtotal = $this->invoice->items()->sum('total_price');
            $this->invoice->update(['subtotal' => $subtotal]);
        }
    }

    /**
     * Scope to filter by invoice.
     */
    public function scopeByInvoice($query, int $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    /**
     * Scope to filter by item type.
     */
    public function scopeByItemType($query, string $itemType)
    {
        return $query->where('item_type', $itemType);
    }
}
