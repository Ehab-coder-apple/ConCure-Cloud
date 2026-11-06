<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_return_id',
        'medicine_transaction_id',
        'medicine_id',
        'quantity_returned',
        'unit_price',
        'line_total',
        'reason_code',
    ];

    protected $casts = [
        'quantity_returned' => 'decimal:2',
        'unit_price'        => 'decimal:2',
        'line_total'        => 'decimal:2',
    ];

    public function invoiceReturn(): BelongsTo
    {
        return $this->belongsTo(InvoiceReturn::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(MedicineTransaction::class, 'medicine_transaction_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
