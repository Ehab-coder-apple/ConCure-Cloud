<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'clinic_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
        'payment_method',
        'notes',
        'terms',
        'created_by',
        'approved_by',
        'sent_at',
        'paid_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Invoice statuses
     */
    const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Payment methods
     */
    const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'card' => 'Credit/Debit Card',
        'bank_transfer' => 'Bank Transfer',
        'insurance' => 'Insurance',
        'other' => 'Other',
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
            // Recalculate totals if relevant fields changed
            if ($invoice->isDirty(['subtotal', 'tax_rate', 'discount_rate', 'discount_amount'])) {
                $invoice->calculateTotals();
            }
            
            // Update status based on payment
            $invoice->updateStatus();
        });
    }

    /**
     * Get the patient that owns the invoice.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the clinic that owns the invoice.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this invoice.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the items for this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the payment method display name.
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'draft' => 'badge bg-secondary',
            'sent' => 'badge bg-warning',
            'paid' => 'badge bg-success',
            'overdue' => 'badge bg-danger',
            'cancelled' => 'badge bg-dark',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('invoice_number', $number)->exists());

        return $number;
    }

    /**
     * Calculate invoice totals.
     */
    public function calculateTotals(): void
    {
        // Calculate tax amount
        if ($this->tax_rate > 0) {
            $this->tax_amount = ($this->subtotal * $this->tax_rate) / 100;
        } else {
            $this->tax_amount = 0;
        }

        // Calculate discount amount if discount rate is provided
        if ($this->discount_rate > 0 && !$this->discount_amount) {
            $this->discount_amount = ($this->subtotal * $this->discount_rate) / 100;
        }

        // Calculate total amount
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        
        // Calculate balance
        $this->balance = $this->total_amount - $this->paid_amount;
    }

    /**
     * Update invoice status based on payment and dates.
     */
    public function updateStatus(): void
    {
        if ($this->status === 'cancelled') {
            return; // Don't change cancelled status
        }

        if ($this->balance <= 0) {
            $this->status = 'paid';
            if (!$this->paid_at) {
                $this->paid_at = now();
            }
        } elseif ($this->status === 'sent' && $this->due_date && $this->due_date->isPast()) {
            $this->status = 'overdue';
        }
    }

    /**
     * Add item to invoice.
     */
    public function addItem(array $itemData): InvoiceItem
    {
        $item = $this->items()->create($itemData);
        
        // Recalculate subtotal
        $this->subtotal = $this->items()->sum('total_price');
        $this->calculateTotals();
        $this->save();
        
        return $item;
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(float $amount, string $paymentMethod = null): void
    {
        $this->update([
            'paid_amount' => $this->paid_amount + $amount,
            'payment_method' => $paymentMethod ?? $this->payment_method,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark invoice as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
               ($this->status === 'sent' && $this->due_date && $this->due_date->isPast());
    }

    /**
     * Check if invoice can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft']);
    }

    /**
     * Check if invoice can be sent.
     */
    public function canBeSent(): bool
    {
        return $this->status === 'draft' && $this->items()->count() > 0;
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'overdue')
              ->orWhere(function ($sq) {
                  $sq->where('status', 'sent')
                     ->whereNotNull('due_date')
                     ->where('due_date', '<', now());
              });
        });
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter recent invoices.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('invoice_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by invoice date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('invoice_date', 'desc');
    }
}
