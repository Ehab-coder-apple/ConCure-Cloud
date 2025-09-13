<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DietPlanMeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'diet_plan_id',
        'day_number',
        'meal_type',
        'option_number',
        'is_option_based',
        'option_description',
        'meal_name',
        'instructions',
        'suggested_time',
    ];

    protected $casts = [
        'suggested_time' => 'datetime:H:i',
    ];

    /**
     * Meal types
     */
    const MEAL_TYPES = [
        'breakfast' => 'Breakfast',
        'lunch' => 'Lunch',
        'dinner' => 'Dinner',
        'snack_1' => 'Morning Snack',
        'snack_2' => 'Afternoon Snack',
        'snack_3' => 'Evening Snack',
    ];

    /**
     * Get the diet plan that owns this meal.
     */
    public function dietPlan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class);
    }

    /**
     * Get the foods for this meal.
     */
    public function foods(): HasMany
    {
        return $this->hasMany(DietPlanMealFood::class);
    }

    /**
     * Get the meal type display name.
     */
    public function getMealTypeDisplayAttribute(): string
    {
        return self::MEAL_TYPES[$this->meal_type] ?? $this->meal_type;
    }

    /**
     * Get the meal name or default based on type.
     */
    public function getMealNameDisplayAttribute(): string
    {
        return $this->meal_name ?: $this->meal_type_display;
    }

    /**
     * Get the suggested time formatted.
     */
    public function getSuggestedTimeFormattedAttribute(): string
    {
        return $this->suggested_time ? $this->suggested_time->format('H:i') : '';
    }

    /**
     * Add food to meal.
     */
    public function addFood(array $foodData): DietPlanMealFood
    {
        return $this->foods()->create($foodData);
    }

    /**
     * Calculate total calories for this meal.
     */
    public function getTotalCaloriesAttribute(): float
    {
        return $this->foods->sum(function ($food) {
            if ($food->food && $food->quantity) {
                // Calculate calories based on food's calories per 100g and quantity
                return ($food->food->calories * $food->quantity) / 100;
            }
            return 0;
        });
    }

    /**
     * Calculate total protein for this meal.
     */
    public function getTotalProteinAttribute(): float
    {
        return $this->foods->sum(function ($food) {
            if ($food->food && $food->quantity) {
                return ($food->food->protein * $food->quantity) / 100;
            }
            return 0;
        });
    }

    /**
     * Calculate total carbs for this meal.
     */
    public function getTotalCarbsAttribute(): float
    {
        return $this->foods->sum(function ($food) {
            if ($food->food && $food->quantity) {
                return ($food->food->carbohydrates * $food->quantity) / 100;
            }
            return 0;
        });
    }

    /**
     * Calculate total fat for this meal.
     */
    public function getTotalFatAttribute(): float
    {
        return $this->foods->sum(function ($food) {
            if ($food->food && $food->quantity) {
                return ($food->food->fat * $food->quantity) / 100;
            }
            return 0;
        });
    }

    /**
     * Scope to filter by diet plan.
     */
    public function scopeByDietPlan($query, int $dietPlanId)
    {
        return $query->where('diet_plan_id', $dietPlanId);
    }

    /**
     * Scope to filter by day.
     */
    public function scopeByDay($query, int $dayNumber)
    {
        return $query->where('day_number', $dayNumber);
    }

    /**
     * Scope to filter by meal type.
     */
    public function scopeByMealType($query, string $mealType)
    {
        return $query->where('meal_type', $mealType);
    }

    /**
     * Scope to order by day and meal type.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('day_number')
                    ->orderByRaw("FIELD(meal_type, 'breakfast', 'snack_1', 'lunch', 'snack_2', 'dinner', 'snack_3')")
                    ->orderBy('option_number');
    }

    /**
     * Scope to filter by option number.
     */
    public function scopeByOption($query, int $optionNumber)
    {
        return $query->where('option_number', $optionNumber);
    }

    /**
     * Scope to filter option-based meals.
     */
    public function scopeOptionBased($query)
    {
        return $query->where('is_option_based', true);
    }

    /**
     * Scope to filter day-based meals.
     */
    public function scopeDayBased($query)
    {
        return $query->where('is_option_based', false);
    }

    /**
     * Scope to order by meal type and option number for option-based plans.
     */
    public function scopeOrderedByOptions($query)
    {
        return $query->orderByRaw("FIELD(meal_type, 'breakfast', 'snack_1', 'lunch', 'snack_2', 'dinner', 'snack_3')")
                    ->orderBy('option_number');
    }

    /**
     * Get the option display name.
     */
    public function getOptionDisplayAttribute(): string
    {
        if ($this->is_option_based) {
            return $this->option_description ?: "Option {$this->option_number}";
        }
        return "Day {$this->day_number}";
    }

    /**
     * Get the full meal display name including option.
     */
    public function getFullMealNameAttribute(): string
    {
        $baseName = $this->meal_name_display;
        if ($this->is_option_based) {
            return "{$baseName} - {$this->option_display}";
        }
        return "{$baseName} (Day {$this->day_number})";
    }
}
