<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AestheticInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'clinic_id',
        'invoice_number',
        'patient_id',
        'session_id',
        'patient_package_id',
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
        'notes',
        'paid_at',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'paid' => 'Paid',
        'partial' => 'Partially Paid',
        'overdue' => 'Overdue',
        'cancelled' => 'Cancelled',
    ];

    const STATUS_COLORS = [
        'draft' => 'secondary',
        'sent' => 'info',
        'paid' => 'success',
        'partial' => 'warning',
        'overdue' => 'danger',
        'cancelled' => 'dark',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }
        });

        static::creating(function ($invoice) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            $clinicId = auth()->check() ? auth()->user()->clinic_id : null;
            if ($tenantId) {
                $invoice->tenant_id = $tenantId;
            }
            if ($clinicId) {
                $invoice->clinic_id = $clinicId;
            }
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            if (!$invoice->invoice_date) {
                $invoice->invoice_date = now()->toDateString();
            }
            $invoice->calculateTotals();
        });

        static::updating(function ($invoice) {
            if ($invoice->isDirty(['subtotal', 'tax_rate', 'discount_amount'])) {
                $invoice->calculateTotals();
            }
        });
    }

    /**
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'AEST-';
        $date = now()->format('Ymd');
        $lastInvoice = self::withTrashed()
            ->where('invoice_number', 'like', "{$prefix}{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;
        return sprintf("%s%s-%04d", $prefix, $date, $sequence);
    }

    /**
     * Get the patient that owns the invoice.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the session associated with the invoice.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AestheticSession::class, 'session_id');
    }

    /**
     * Get the patient package associated with the invoice.
     */
    public function patientPackage(): BelongsTo
    {
        return $this->belongsTo(PatientPackage::class);
    }

    /**
     * Get the creator of the invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the items for this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(AestheticInvoiceItem::class, 'invoice_id');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter overdue invoices.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'overdue')
              ->orWhere(function ($sq) {
                  $sq->whereIn('status', ['draft', 'sent'])
                     ->whereNotNull('due_date')
                     ->where('due_date', '<', now()->toDateString());
              });
        });
    }

    /**
     * Scope by date range.
     */
    public function scopeByDateRange(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('invoice_date', [$start, $end]);
    }

    /**
     * Calculate invoice totals.
     */
    public function calculateTotals(): void
    {
        if ($this->tax_rate > 0) {
            $this->tax_amount = ($this->subtotal * $this->tax_rate) / 100;
        } else {
            $this->tax_amount = 0;
        }

        $this->total_amount = max(0, $this->subtotal + $this->tax_amount - $this->discount_amount);
        $this->balance = max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Add an item to the invoice.
     */
    public function addItem(array $data): AestheticInvoiceItem
    {
        $data['total_price'] = ($data['quantity'] * $data['unit_price']) - ($data['discount'] ?? 0);
        $item = $this->items()->create($data);
        $this->recalculateSubtotal();
        return $item;
    }

    /**
     * Recalculate subtotal from items.
     */
    public function recalculateSubtotal(): void
    {
        $this->subtotal = $this->items()->sum('total_price');
        $this->calculateTotals();
        $this->save();
    }

    /**
     * Record a payment.
     */
    public function recordPayment(float $amount, ?string $method = null): void
    {
        $this->paid_amount = (float) $this->paid_amount + $amount;
        $this->balance = max(0, $this->total_amount - $this->paid_amount);

        if ($this->balance <= 0.01) {
            $this->status = 'paid';
            if (!$this->paid_at) {
                $this->paid_at = now();
            }
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        }

        if ($method) {
            $this->payment_method = $method;
        }

        $this->save();
    }
}
