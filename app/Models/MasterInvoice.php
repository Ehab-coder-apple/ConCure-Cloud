<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'clinic_id',
        'currency',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
        'payment_method',
        'payment_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'paid' => 'Paid',
        'partial' => 'Partially Paid',
        'overdue' => 'Overdue',
        'cancelled' => 'Cancelled',
    ];

    const CURRENCIES = [
        'USD' => '$',
        'IQD' => 'IQD',
        'JOD' => 'JD',
        'EGP' => 'EGP',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            
            if (!$invoice->invoice_date) {
                $invoice->invoice_date = now()->toDateString();
            }
            
            // Calculate totals
            $invoice->calculateTotals();
        });

        static::updating(function ($invoice) {
            if ($invoice->isDirty(['subtotal', 'tax_rate', 'discount_amount'])) {
                $invoice->calculateTotals();
            }
        });
    }

    /**
     * Get the clinic this invoice belongs to.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the invoice items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MasterInvoiceItem::class);
    }

    /**
     * Get the user who created this invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastInvoice = self::whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return sprintf('INV-%s%s-%04d', $year, $month, $sequence);
    }

    /**
     * Calculate invoice totals and reconcile the status.
     *
     * Status resolution must be deterministic — the previous version
     * stamped status='paid' on a brand-new empty invoice (balance defaults
     * to 0 before items are added) and then never demoted it because the
     * subsequent branches were gated on `status !== 'paid'`. That left
     * unpaid invoices labelled "PAID" in the master finance list.
     *
     * The rules below converge to exactly one status every call:
     *   - draft / cancelled  → preserved (manual states are not auto-changed)
     *   - paid_amount >= total_amount AND total_amount > 0 → paid
     *   - paid_amount > 0    → partial
     *   - due_date in the past → overdue
     *   - otherwise          → sent
     */
    public function calculateTotals(): void
    {
        $this->tax_amount = ($this->subtotal * $this->tax_rate) / 100;
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->balance = $this->total_amount - $this->paid_amount;

        if (in_array($this->status, ['draft', 'cancelled'], true)) {
            return;
        }

        if ((float) $this->total_amount > 0 && (float) $this->paid_amount >= (float) $this->total_amount) {
            $this->status = 'paid';
            return;
        }

        if ((float) $this->paid_amount > 0) {
            $this->status = 'partial';
            return;
        }

        if ($this->due_date && $this->due_date < now()->startOfDay()) {
            $this->status = 'overdue';
            return;
        }

        $this->status = 'sent';
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(float $amount, string $paymentMethod, $paymentDate = null): void
    {
        $this->paid_amount = $amount;
        $this->payment_method = $paymentMethod;
        $this->payment_date = $paymentDate ?? now();
        $this->calculateTotals();
        $this->save();
    }

    /**
     * Get currency symbol.
     */
    public function getCurrencySymbol(): string
    {
        return self::CURRENCIES[$this->currency] ?? '$';
    }

    /**
     * Get currency symbol for a given currency code.
     */
    public static function getCurrencySymbolStatic(string $currency): string
    {
        return self::CURRENCIES[$currency] ?? '$';
    }
}
