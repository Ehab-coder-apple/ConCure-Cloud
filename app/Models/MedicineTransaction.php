<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'clinic_id',
        'user_id',
        'type',
        'quantity',
        'unit_price',
        'total_amount',
        'reference_number',
        'patient_id',
        'supplier_name',
        'payment_method',
        'stock_before',
        'stock_after',
        'notes',
        'transaction_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Get the medicine for this transaction.
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Get the clinic for this transaction.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who performed the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the patient for sales transactions.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Scope to filter sales transactions.
     */
    public function scopeSales($query)
    {
        return $query->where('type', 'sale');
    }

    /**
     * Scope to filter purchase transactions.
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Check if this is a sale transaction.
     */
    public function isSale(): bool
    {
        return $this->type === 'sale';
    }

    /**
     * Check if this is a purchase transaction.
     */
    public function isPurchase(): bool
    {
        return $this->type === 'purchase';
    }

    /**
     * Get transaction type badge color.
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return $this->type === 'sale' ? 'danger' : 'success';
    }

    /**
     * Get transaction type display name.
     */
    public function getTypeDisplayAttribute(): string
    {
        return ucfirst($this->type);
    }
}
