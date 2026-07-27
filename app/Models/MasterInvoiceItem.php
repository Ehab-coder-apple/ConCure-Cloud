<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class MasterInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });

        static::updating(function ($item) {
            if ($item->isDirty(['quantity', 'unit_price'])) {
                $item->total_price = $item->quantity * $item->unit_price;
            }
        });

        static::saved(function ($item) {
            $item->updateInvoiceSubtotal();
        });

        static::deleted(function ($item) {
            $item->updateInvoiceSubtotal();
        });
    }

    /**
     * Get the invoice this item belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(MasterInvoice::class, 'master_invoice_id');
    }

    /**
     * Update the invoice subtotal.
     */
    private function updateInvoiceSubtotal(): void
    {
        if ($this->invoice) {
            $subtotal = $this->invoice->items()->sum('total_price');

            DB::table('master_invoices')
                ->where('id', $this->master_invoice_id)
                ->update([
                    'subtotal' => $subtotal,
                    'updated_at' => now(),
                ]);

            $this->invoice->subtotal = $subtotal;
        }
    }
}
