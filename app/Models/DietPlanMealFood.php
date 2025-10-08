<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietPlanMealFood extends Model
{
    use HasFactory;

    protected $table = 'diet_plan_meal_foods';

    protected $fillable = [
        'diet_plan_meal_id',
        'food_id',
        'food_name',
        'quantity',
        'unit',
        'preparation_notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    /**
     * Common units
     */
    const UNITS = [
        'g' => 'grams',
        'kg' => 'kilograms',
        'ml' => 'milliliters',
        'l' => 'liters',
        'cup' => 'cup',
        'tbsp' => 'tablespoon',
        'tsp' => 'teaspoon',
        'piece' => 'piece',
        'slice' => 'slice',
        'serving' => 'serving',
    ];

    /**
     * Get the diet plan meal that owns this food.
     */
    public function dietPlanMeal(): BelongsTo
    {
        return $this->belongsTo(DietPlanMeal::class);
    }

    /**
     * Get the food (if selected from database).
     */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    /**
     * Get the food name (from database or custom).
     */
    public function getFoodNameDisplayAttribute(): string
    {
        return $this->food ? $this->food->name : $this->food_name;
    }

    /**
     * Get the unit display name.
     */
    public function getUnitDisplayAttribute(): string
    {
        return self::UNITS[$this->unit] ?? $this->unit;
    }

    /**
     * Get formatted quantity with unit.
     */
    public function getQuantityFormattedAttribute(): string
    {
        return $this->quantity . ' ' . $this->unit_display;
    }

    /**
     * Calculate calories for this food item.
     */
    public function getCaloriesAttribute(): float
    {
        if ($this->food && $this->quantity) {
            // Calculate calories based on food's calories per 100g and quantity
            return ($this->food->calories * $this->quantity) / 100;
        }
        return 0;
    }

    /**
     * Calculate protein for this food item.
     */
    public function getProteinAttribute(): float
    {
        if ($this->food && $this->quantity) {
            return ($this->food->protein * $this->quantity) / 100;
        }
        return 0;
    }

    /**
     * Calculate carbs for this food item.
     */
    public function getCarbsAttribute(): float
    {
        if ($this->food && $this->quantity) {
            return ($this->food->carbohydrates * $this->quantity) / 100;
        }
        return 0;
    }

    /**
     * Calculate fat for this food item.
     */
    public function getFatAttribute(): float
    {
        if ($this->food && $this->quantity) {
            return ($this->food->fat * $this->quantity) / 100;
        }
        return 0;
    }

    /**
     * Get nutritional summary.
     */
    public function getNutritionalSummaryAttribute(): array
    {
        return [
            'calories' => round($this->calories, 1),
            'protein' => round($this->protein, 1),
            'carbs' => round($this->carbs, 1),
            'fat' => round($this->fat, 1),
        ];
    }
    /**
     * Format a number: drop trailing .00, keep up to 2 decimals when needed.
     */
    protected static function formatNumber($value): string
    {
        if ($value === null || $value === '') { return ''; }
        $n = (float) $value;
        if (abs($n - round($n)) < 0.00001) {
            return (string) (int) round($n);
        }
        $s = number_format($n, 2, '.', '');
        $s = rtrim(rtrim($s, '0'), '.');
        return $s;
    }

    /**
     * Calculate approximate milliliters for current quantity/unit (for volume units).
     */
    protected function calcMlForCurrent(): ?float
    {
        $unit = strtolower(trim((string) ($this->unit ?? '')));
        $qty = (float) ($this->quantity ?? 0);
        if ($qty <= 0) { return null; }
        return match ($unit) {
            'l' => $qty * 1000.0,
            'ml' => $qty,
            'cup' => $qty * 240.0,
            'tbsp' => $qty * 15.0,
            'tsp' => $qty * 5.0,
            default => null,
        };
    }

    /**
     * Calculate approximate grams for current quantity/unit (leverages Food::convertToGrams when possible).
     */
    protected function calcGramsForCurrent(): ?float
    {
        $qty = (float) ($this->quantity ?? 0);
        if ($qty <= 0) { return null; }
        $food = $this->food;
        if (!$food) { return null; }
        $unit = strtolower(trim((string) ($this->unit ?? 'g')));
        try {
            return (float) $food->convertToGrams($qty, $unit);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Clean quantity without unnecessary trailing decimals.
     */
    public function getQuantityCleanAttribute(): string
    {
        return self::formatNumber($this->quantity);
    }

    /**
     * Equivalent text like (~ 150 g) or (~ 240 ml), when applicable.
     * Skips when the unit already expresses mass/volume directly (g, kg, mg, ml, l).
     */
    public function getEquivalentTextAttribute(): string
    {
        // Always display both grams and milliliters together; assume 1 g == 1 ml when only one is known
        $grams = $this->calcGramsForCurrent();
        $ml = $this->calcMlForCurrent();

        $hasG = ($grams !== null && $grams > 0);
        $hasMl = ($ml !== null && $ml > 0);

        if (!$hasG && $hasMl) { $grams = $ml; $hasG = true; }
        if (!$hasMl && $hasG) { $ml = $grams; $hasMl = true; }

        $gStr = $hasG ? self::formatNumber($grams) : '';
        $mlStr = $hasMl ? self::formatNumber($ml) : '';

        return '(~ ' . $gStr . ' g / ' . $mlStr . ' ml)';
    }

    /**
     * Quantity + unit + optional equivalent text.
     */
    public function getQuantityWithEquivalentAttribute(): string
    {
        $qty = $this->quantity_clean;
        $u = trim((string) ($this->unit ?? ''));
        $base = trim($qty . ($u !== '' ? (' ' . $u) : ''));
        $eq = $this->equivalent_text;
        return $base . ($eq ? (' ' . $eq) : '');
    }


    /**
     * Scope to filter by diet plan meal.
     */
    public function scopeByDietPlanMeal($query, int $dietPlanMealId)
    {
        return $query->where('diet_plan_meal_id', $dietPlanMealId);
    }

    /**
     * Scope to filter by food.
     */
    public function scopeByFood($query, int $foodId)
    {
        return $query->where('food_id', $foodId);
    }
}
