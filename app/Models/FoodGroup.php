<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_translations',
        'description',
        'description_translations',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the foods for the food group.
     */
    public function foods(): HasMany
    {
        return $this->hasMany(Food::class);
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
     * Get the translated description for current locale.
     */
    public function getTranslatedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $this->description_translations[$locale] ?? $this->description;
    }

    /**
     * Get the food count for this group.
     */
    public function getFoodCountAttribute(): int
    {
        return $this->foods()->active()->count();
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
     * Scope to filter active food groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope to search food groups.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereRaw("JSON_EXTRACT(name_translations, '$.en') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(name_translations, '$.ar') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(name_translations, '$.ku') LIKE ?", ["%{$search}%"]);
        });
    }
}
