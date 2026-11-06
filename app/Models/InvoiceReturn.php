<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceReturn extends Model
{
    use HasFactory;

    /**
     * Fixed reason codes for the "Reason for Return" dropdown.
     * Keep in sync with resources/views/medicines/sales/* return modal.
     */
    public const REASON_CODES = [
        'expired_medication'        => 'Expired Medication',
        'wrong_medication_dispensed' => 'Wrong Medication Dispensed',
        'patient_cancelled_refused' => 'Patient Cancelled/Refused',
        'defective_product'         => 'Defective Product',
    ];

    protected $fillable = [
        'clinic_id',
        'invoice_id',
        'returned_by_user_id',
        'reason_code',
        'notes',
        'refund_amount',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(MedicineSaleInvoice::class, 'invoice_id');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function reasonLabel(): string
    {
        return self::REASON_CODES[$this->reason_code] ?? $this->reason_code;
    }
}
