<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'clinic_id',
        'description',
        'amount',
        'category',
        'receipt_date',
        'payment_method',
        'payer_name',
        'reference_number',
        'receipt_file',
        'notes',
        'created_by',
        'approved_by',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'receipt_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Receipt categories
     */
    const CATEGORIES = [
        'consultation_fee' => 'Consultation Fee',
        'procedure_fee' => 'Procedure Fee',
        'medication_sale' => 'Medication Sale',
        'lab_test_fee' => 'Lab Test Fee',
        'equipment_rental' => 'Equipment Rental',
        'insurance_reimbursement' => 'Insurance Reimbursement',
        'donation' => 'Donation',
        'refund' => 'Refund',
        'other' => 'Other',
    ];

    /**
     * Payment methods
     */
    const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'card' => 'Card',
        'bank_transfer' => 'Bank Transfer',
        'check' => 'Check',
        'other' => 'Other',
    ];

    /**
     * Status options
     */
    const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            if (!$receipt->receipt_number) {
                $receipt->receipt_number = self::generateReceiptNumber();
            }

            if (!$receipt->receipt_date) {
                $receipt->receipt_date = now()->toDateString();
            }
        });

        static::deleting(function ($receipt) {
            // Delete receipt file when receipt is deleted
            if ($receipt->receipt_file && Storage::exists($receipt->receipt_file)) {
                Storage::delete($receipt->receipt_file);
            }
        });
    }

    /**
     * Get the clinic that owns the receipt.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created the receipt.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the receipt.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('receipt_date', [$startDate, $endDate]);
    }

    /**
     * Scope for pending receipts.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved receipts.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get the receipt file URL.
     */
    public function getReceiptFileUrlAttribute(): ?string
    {
        return $this->receipt_file ? Storage::url($this->receipt_file) : null;
    }

    /**
     * Check if receipt file exists.
     */
    public function hasReceiptFile(): bool
    {
        return $this->receipt_file && Storage::exists($this->receipt_file);
    }

    /**
     * Generate a unique receipt number.
     */
    public static function generateReceiptNumber(): string
    {
        do {
            $number = 'RCP-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('receipt_number', $number)->exists());

        return $number;
    }

    /**
     * Mark receipt as approved.
     */
    public function markAsApproved(User $approver): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark receipt as rejected.
     */
    public function markAsRejected(): void
    {
        $this->update([
            'status' => 'rejected',
        ]);
    }

    /**
     * Get formatted category name.
     */
    public function getCategoryNameAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Get formatted payment method name.
     */
    public function getPaymentMethodNameAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /**
     * Get formatted status name.
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
