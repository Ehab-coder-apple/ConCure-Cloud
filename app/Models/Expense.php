<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'clinic_id',
        'description',
        'amount',
        'category',
        'expense_date',
        'payment_method',
        'vendor_name',
        'receipt_number',
        'receipt_file',
        'notes',
        'is_recurring',
        'recurring_frequency',
        'created_by',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Expense categories
     */
    const CATEGORIES = [
        'salary' => 'Salary',
        'rent' => 'Rent',
        'utilities' => 'Utilities',
        'equipment' => 'Equipment',
        'supplies' => 'Supplies',
        'marketing' => 'Marketing',
        'insurance' => 'Insurance',
        'taxes' => 'Taxes',
        'maintenance' => 'Maintenance',
        'other' => 'Other',
    ];

    /**
     * Payment methods
     */
    const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'card' => 'Credit/Debit Card',
        'bank_transfer' => 'Bank Transfer',
        'check' => 'Check',
        'other' => 'Other',
    ];

    /**
     * Expense statuses
     */
    const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    /**
     * Recurring frequencies
     */
    const RECURRING_FREQUENCIES = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (!$expense->expense_number) {
                $expense->expense_number = self::generateExpenseNumber();
            }
            
            if (!$expense->expense_date) {
                $expense->expense_date = now()->toDateString();
            }
        });

        static::deleting(function ($expense) {
            // Delete receipt file when expense is deleted
            if ($expense->receipt_file && Storage::exists($expense->receipt_file)) {
                Storage::delete($expense->receipt_file);
            }
        });
    }

    /**
     * Get the clinic that owns the expense.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this expense.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this expense.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the category display name.
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get the payment method display name.
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the recurring frequency display name.
     */
    public function getRecurringFrequencyDisplayAttribute(): string
    {
        return self::RECURRING_FREQUENCIES[$this->recurring_frequency] ?? $this->recurring_frequency;
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge bg-warning',
            'approved' => 'badge bg-success',
            'rejected' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
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
     * Generate a unique expense number.
     */
    public static function generateExpenseNumber(): string
    {
        do {
            $number = 'EXP-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('expense_number', $number)->exists());

        return $number;
    }

    /**
     * Mark expense as approved.
     */
    public function markAsApproved(User $approver): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
        ]);
    }

    /**
     * Mark expense as rejected.
     */
    public function markAsRejected(User $approver): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
        ]);
    }

    /**
     * Check if expense can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if expense can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending expenses.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter approved expenses.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to filter recurring expenses.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter recent expenses.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('expense_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by expense date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('expense_date', 'desc');
    }
}
