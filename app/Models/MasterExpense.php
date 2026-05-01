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
        'city',
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
        if (isset(self::CATEGORIES[$this->category])) {
            return self::CATEGORIES[$this->category];
        }

        // Fall back to a custom category label when one is recorded.
        if (\Illuminate\Support\Facades\Schema::hasTable('master_expense_categories')) {
            $custom = MasterExpenseCategory::where('key', $this->category)->value('label');
            if ($custom) {
                return $custom;
            }
        }

        return ucfirst((string) $this->category);
    }

    /**
     * Merge the built-in CATEGORIES with custom platform categories so they all
     * appear in the same dropdown. 'Other' is always rendered last so the new
     * "add custom category" affordance has a stable home in the picker.
     */
    public static function categoriesAll(): array
    {
        $builtIn = self::CATEGORIES;
        $other = $builtIn['other'] ?? 'Other';
        unset($builtIn['other']);

        $custom = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('master_expense_categories')) {
            $custom = MasterExpenseCategory::orderBy('label')
                ->pluck('label', 'key')
                ->all();
        }

        // Custom keys win over built-ins of the same slug (built-ins shouldn't
        // collide, but keep the operator's renamed value if they ever do).
        return array_merge($builtIn, $custom, ['other' => $other]);
    }
}
