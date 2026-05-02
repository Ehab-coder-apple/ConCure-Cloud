<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineSaleInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'user_id',
        'patient_id',
        'invoice_number',
        'payment_method',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid_amount',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'tax'         => 'decimal:2',
        'total'       => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'sold_at'     => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicineTransaction::class, 'medicine_sale_invoice_id');
    }

    /**
     * Generate a clinic-scoped, collision-resistant invoice number.
     */
    public static function generateInvoiceNumber(int $clinicId): string
    {
        return 'MS-' . date('Ymd') . '-' . str_pad((string) $clinicId, 4, '0', STR_PAD_LEFT)
            . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }
}
