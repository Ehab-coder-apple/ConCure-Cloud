<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods';

    protected $fillable = [
        'name',
        'name_translations',
        'food_group_id',
        'description',
        'description_translations',
        'calories',
        'protein',
        'carbohydrates',
        'fat',
        'fiber',
        'sugar',
        'sodium',
        'potassium',
        'calcium',
        'iron',
        'vitamin_c',
        'vitamin_a',
        'serving_size',
        'serving_weight',
        'is_custom',
        'clinic_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'calories' => 'decimal:2',
        'protein' => 'decimal:2',
        'carbohydrates' => 'decimal:2',
        'fat' => 'decimal:2',
        'fiber' => 'decimal:2',
        'sugar' => 'decimal:2',
        'sodium' => 'decimal:2',
        'potassium' => 'decimal:2',
        'calcium' => 'decimal:2',
        'iron' => 'decimal:2',
        'vitamin_c' => 'decimal:2',
        'vitamin_a' => 'decimal:2',
        'serving_weight' => 'decimal:2',
        'is_custom' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the food group that owns the food.
     */
    public function foodGroup(): BelongsTo
    {
        return $this->belongsTo(FoodGroup::class);
    }

    /**
     * Get the clinic that owns the food (for custom foods).
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this food.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the diet plan meal foods for this food.
     */
    public function dietPlanMealFoods(): HasMany
    {
        return $this->hasMany(DietPlanMealFood::class);
    }

    /**
     * Get the translated name for current locale.
     */
    public function getTranslatedNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->name_translations[$locale] ?? $this->name;
    }

    /**
     * Get the translated name for a specific language.
     */
    public function getNameInLanguage(string $language): string
    {
        return $this->name_translations[$language] ?? $this->name;
    }

    /**
     * Get all available translations for the food name.
     */
    public function getAllNameTranslations(): array
    {
        $translations = $this->name_translations ?? [];

        // Ensure we have the base name as fallback
        if (!isset($translations['en'])) {
            $translations['en'] = $this->name;
        }

        return $translations;
    }

    /**
     * Get supported languages for food names.
     */
    public static function getSupportedLanguages(): array
    {
        return [
            'en' => 'English',
            'ar' => 'العربية',
            'ku_bahdini' => 'کوردی بادینی',
            'ku_sorani' => 'کوردی سۆرانی'
        ];
    }

    /**
     * Get the translated description for current locale.
     */
    public function getTranslatedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $this->description_translations[$locale] ?? $this->description;
    }

    /**
     * Get nutritional summary per 100g.
     */
    public function getNutritionalSummaryAttribute(): array
    {
        return [
            'calories' => $this->calories,
            'protein' => $this->protein,
            'carbohydrates' => $this->carbohydrates,
            'fat' => $this->fat,
            'fiber' => $this->fiber,
            'sugar' => $this->sugar,
        ];
    }

    /**
     * Get mineral content summary.
     */
    public function getMineralContentAttribute(): array
    {
        return [
            'sodium' => $this->sodium,
            'potassium' => $this->potassium,
            'calcium' => $this->calcium,
            'iron' => $this->iron,
        ];
    }

    /**
     * Get vitamin content summary.
     */
    public function getVitaminContentAttribute(): array
    {
        return [
            'vitamin_c' => $this->vitamin_c,
            'vitamin_a' => $this->vitamin_a,
        ];
    }

    /**
     * Calculate nutrition for a specific quantity.
     */
    public function calculateNutrition(float $quantity, string $unit = 'g'): array
    {
        // Convert quantity to grams if needed
        $quantityInGrams = $this->convertToGrams($quantity, $unit);
        
        // Calculate nutrition per quantity (base is per 100g)
        $factor = $quantityInGrams / 100;
        
        return [
            'calories' => round($this->calories * $factor, 1),
            'protein' => round($this->protein * $factor, 1),
            'carbohydrates' => round($this->carbohydrates * $factor, 1),
            'fat' => round($this->fat * $factor, 1),
            'fiber' => round($this->fiber * $factor, 1),
            'sugar' => round($this->sugar * $factor, 1),
            'sodium' => round($this->sodium * $factor, 1),
            'potassium' => round($this->potassium * $factor, 1),
            'calcium' => round($this->calcium * $factor, 1),
            'iron' => round($this->iron * $factor, 1),
            'vitamin_c' => round($this->vitamin_c * $factor, 1),
            'vitamin_a' => round($this->vitamin_a * $factor, 1),
        ];
    }

    /**
     * Convert quantity to grams.
     */
    private function convertToGrams(float $quantity, string $unit): float
    {
        return match($unit) {
            'kg' => $quantity * 1000,
            'g' => $quantity,
            'mg' => $quantity / 1000,
            'cup' => $quantity * 240, // Approximate
            'tbsp' => $quantity * 15,
            'tsp' => $quantity * 5,
            'serving' => $this->serving_weight ? $quantity * $this->serving_weight : $quantity * 100,
            default => $quantity, // Assume grams
        };
    }

    /**
     * Set name translation for a specific locale.
     */
    public function setNameTranslation(string $locale, string $name): void
    {
        $translations = $this->name_translations ?? [];
        $translations[$locale] = $name;
        $this->name_translations = $translations;
    }

    /**
     * Set description translation for a specific locale.
     */
    public function setDescriptionTranslation(string $locale, string $description): void
    {
        $translations = $this->description_translations ?? [];
        $translations[$locale] = $description;
        $this->description_translations = $translations;
    }

    /**
     * Get name translation for a specific locale.
     */
    public function getNameTranslation(string $locale): ?string
    {
        return $this->name_translations[$locale] ?? null;
    }

    /**
     * Get description translation for a specific locale.
     */
    public function getDescriptionTranslation(string $locale): ?string
    {
        return $this->description_translations[$locale] ?? null;
    }

    /**
     * Scope to filter active foods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by food group.
     */
    public function scopeByFoodGroup($query, int $foodGroupId)
    {
        return $query->where('food_group_id', $foodGroupId);
    }

    /**
     * Scope to filter custom foods.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_custom', true);
    }

    /**
     * Scope to filter standard foods.
     */
    public function scopeStandard($query)
    {
        return $query->where('is_custom', false);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to search foods.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereRaw("JSON_EXTRACT(name_translations, '$.en') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(name_translations, '$.ar') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(name_translations, '$.ku_bahdini') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(name_translations, '$.ku_sorani') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(name_translations, '$.ku') LIKE ?", ["%{$search}%"]); // Legacy support
        });
    }

    /**
     * Scope to filter high protein foods.
     */
    public function scopeHighProtein($query, float $minProtein = 10)
    {
        return $query->where('protein', '>=', $minProtein);
    }

    /**
     * Scope to filter low calorie foods.
     */
    public function scopeLowCalorie($query, float $maxCalories = 100)
    {
        return $query->where('calories', '<=', $maxCalories);
    }

    /**
     * Scope to filter high fiber foods.
     */
    public function scopeHighFiber($query, float $minFiber = 5)
    {
        return $query->where('fiber', '>=', $minFiber);
    }
}
