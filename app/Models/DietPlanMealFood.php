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
