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
     * Resolve the Food record for nutrition calculations, even when this entry
     * was saved without a food_id (e.g. typed/auto-suggested by name). Falls
     * back to an exact, case-insensitive name match against the Food database
     * (scoped to standard foods or the owning clinic's custom foods) so
     * calories/macros can still be calculated for these entries.
     */
    public function getResolvedFoodAttribute(): ?Food
    {
        if ($this->food) {
            return $this->food;
        }
        if ($this->food_id) {
            // food_id set but relation resolved to null (e.g. dangling reference)
            return null;
        }

        $name = trim((string) $this->food_name);
        if ($name === '') {
            return null;
        }

        $clinicId = $this->dietPlanMeal?->dietPlan?->patient?->clinic_id;

        $query = Food::where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);

        if ($clinicId) {
            $query->where(function ($q) use ($clinicId) {
                $q->where('is_custom', false)->orWhere('clinic_id', $clinicId);
            });
        } else {
            $query->where('is_custom', false);
        }

        return $query->first();
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
     * Calculate calories for this food item (unit-aware via grams equivalent).
     */
    public function getCaloriesAttribute(): float
    {
        $food = $this->resolved_food;
        if ($food && $this->quantity) {
            $grams = $this->calcGramsForCurrent();
            if ($grams === null) { return 0; }
            return ($food->calories * $grams) / 100.0;
        }
        return 0.0;
    }

    /**
     * Calculate protein for this food item (unit-aware).
     */
    public function getProteinAttribute(): float
    {
        $food = $this->resolved_food;
        if ($food && $this->quantity) {
            $grams = $this->calcGramsForCurrent();
            if ($grams === null) { return 0.0; }
            return ($food->protein * $grams) / 100.0;
        }
        return 0.0;
    }

    /**
     * Calculate carbs for this food item (unit-aware).
     */
    public function getCarbsAttribute(): float
    {
        $food = $this->resolved_food;
        if ($food && $this->quantity) {
            $grams = $this->calcGramsForCurrent();
            if ($grams === null) { return 0.0; }
            return ($food->carbohydrates * $grams) / 100.0;
        }
        return 0.0;
    }

    /**
     * Calculate fat for this food item (unit-aware).
     */
    public function getFatAttribute(): float
    {
        $food = $this->resolved_food;
        if ($food && $this->quantity) {
            $grams = $this->calcGramsForCurrent();
            if ($grams === null) { return 0.0; }
            return ($food->fat * $grams) / 100.0;
        }
        return 0.0;
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
        $food = $this->resolved_food;
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
     * Equivalent text: show grams only as (~ 150 g).
     * Skip when the unit already expresses mass directly (g, kg, mg).
     */
    public function getEquivalentTextAttribute(): string
    {
        $unit = strtolower(trim((string) ($this->unit ?? '')));
        // If already a mass unit, no equivalent needed
        if (in_array($unit, ['g','kg','mg'], true)) { return ''; }

        $grams = $this->calcGramsForCurrent();
        if ($grams === null || $grams <= 0) { return ''; }

        $gStr = self::formatNumber($grams);
        return '(~ ' . $gStr . ' g)';
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
