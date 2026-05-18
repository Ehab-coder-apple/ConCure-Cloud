<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrthodonticPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orthodontic_case_id',
        'patient_id',
        'clinic_id',
        'payment_date',
        'amount',
        'currency',
        'payment_method',
        'payment_type',
        'installment_number',
        'receipt_number',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'card' => 'Credit/Debit Card',
        'bank_transfer' => 'Bank Transfer',
        'check' => 'Check',
        'insurance' => 'Insurance',
        'other' => 'Other',
    ];

    const PAYMENT_TYPES = [
        'deposit' => 'Deposit',
        'installment' => 'Installment',
        'balance' => 'Balance Payment',
        'adjustment' => 'Adjustment',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            // Update the case paid amount and balance
            $case = $payment->orthodonticCase;
            if ($case) {
                $case->paid_amount = $case->payments()->sum('amount');
                $case->balance = $case->total_cost - $case->paid_amount;
                $case->saveQuietly();
            }
        });

        static::deleted(function ($payment) {
            // Update the case paid amount and balance
            $case = $payment->orthodonticCase;
            if ($case) {
                $case->paid_amount = $case->payments()->sum('amount');
                $case->balance = $case->total_cost - $case->paid_amount;
                $case->saveQuietly();
            }
        });
    }

    // Relationships

    public function orthodonticCase(): BelongsTo
    {
        return $this->belongsTo(OrthodonticCase::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Accessors

    public function getPaymentMethodDisplayAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function getPaymentTypeDisplayAttribute(): string
    {
        return self::PAYMENT_TYPES[$this->payment_type] ?? $this->payment_type;
    }
}
