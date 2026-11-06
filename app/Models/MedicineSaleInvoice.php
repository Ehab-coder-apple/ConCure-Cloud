<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineSaleInvoice extends Model
{
    use HasFactory;

    /**
     * Fixed reason codes for the mandatory "Cancellation Reason" field.
     */
    public const CANCEL_REASON_CODES = [
        'expired_medication'         => 'Expired Medication',
        'wrong_medication_dispensed' => 'Wrong Medication Dispensed',
        'patient_cancelled_refused'  => 'Patient Cancelled/Refused',
        'defective_product'          => 'Defective Product',
        'other'                      => 'Other',
    ];

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_EDITED = 'edited';
    public const STATUS_PARTIALLY_RETURNED = 'partially_returned';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_CANCELLED = 'cancelled';

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
        'status',
        'original_subtotal',
        'original_discount',
        'original_tax',
        'original_total',
        'original_paid_amount',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason_code',
        'cancel_notes',
        'edited_at',
        'edited_by',
    ];

    protected $casts = [
        'subtotal'              => 'decimal:2',
        'discount'              => 'decimal:2',
        'tax'                   => 'decimal:2',
        'total'                 => 'decimal:2',
        'paid_amount'           => 'decimal:2',
        'original_subtotal'     => 'decimal:2',
        'original_discount'     => 'decimal:2',
        'original_tax'          => 'decimal:2',
        'original_total'        => 'decimal:2',
        'original_paid_amount'  => 'decimal:2',
        'sold_at'               => 'datetime',
        'cancelled_at'          => 'datetime',
        'edited_at'             => 'datetime',
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

    public function returns(): HasMany
    {
        return $this->hasMany(InvoiceReturn::class, 'invoice_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Whether this invoice can still accept returns/edits (i.e. not already
     * voided). Fully returned invoices can still be edited (e.g. to fix a
     * price) but cannot be cancelled again.
     */
    public function isMutable(): bool
    {
        return !$this->isCancelled();
    }

    /**
     * Snapshot the as-sold totals the first time the invoice is mutated
     * (returned/edited/cancelled) so the original sale is always auditable.
     */
    public function snapshotOriginalTotalsIfNeeded(): void
    {
        if ($this->original_total !== null) {
            return;
        }

        $this->original_subtotal    = $this->subtotal;
        $this->original_discount    = $this->discount;
        $this->original_tax         = $this->tax;
        $this->original_total       = $this->total;
        $this->original_paid_amount = $this->paid_amount;
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
