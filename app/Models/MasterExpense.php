<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SaaS-platform operational expense (master finance only).
 *
 * Always denominated in IQD — there is intentionally no currency column.
 * Net Profit on the master finance dashboard is computed as
 * Total IQD Revenue minus the sum of these rows for the period.
 */
class MasterExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Fixed category list — keeps the chart breakdown stable and prevents
     * free-text fragmentation. Add new keys here when needed.
     */
    public const CATEGORIES = [
        'hosting' => 'Hosting & Infrastructure',
        'salaries' => 'Salaries & Contractors',
        'marketing' => 'Marketing & Advertising',
        'domains' => 'Domains & Certificates',
        'saas_tools' => 'SaaS Tools & Subscriptions',
        'taxes' => 'Taxes & Fees',
        'other' => 'Other',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Card',
        'other' => 'Other',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Sum the IQD amount in a given period.
     */
    public static function totalForPeriod($from, $to): float
    {
        return (float) static::query()
            ->whereBetween('expense_date', [$from, $to])
            ->sum('amount');
    }

    public function scopeBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
