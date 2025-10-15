<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


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
        'clinic_id',
        'created_by',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Clinic that owns this custom group (null = global/default group)
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * User who created the group
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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
        $translations = $this->name_translations;
        if (is_string($translations)) {
            $decoded = json_decode($translations, true);
            $translations = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($translations)) {
            $translations = [];
        }
        return $translations[$locale] ?? $this->name;
    }

    /**
     * Get the translated description for current locale.
     */
    public function getTranslatedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        $translations = $this->description_translations;
        if (is_string($translations)) {
            $decoded = json_decode($translations, true);
            $translations = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($translations)) {
            $translations = [];
        }
        return $translations[$locale] ?? $this->description;
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
     * Scope: groups available for a clinic (global or owned by clinic)
     */
    public function scopeForClinic($query, ?int $clinicId)
    {
        if (!$clinicId) {
            // No clinic context: show global defaults
            return $query->whereNull('clinic_id');
        }
        // Include global defaults (clinic_id null) and this clinic's custom groups
        return $query->where(function ($q) use ($clinicId) {
            $q->whereNull('clinic_id')
              ->orWhere('clinic_id', $clinicId);
        });
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

    /**
     * Canonical key used to deduplicate similar groups (e.g., Fat vs Fats & Oils).
     */
    public function getCanonicalKeyAttribute(): string
    {
        $name = strtolower(trim($this->name));
        $name = preg_replace('/\s+/', ' ', $name);
        // Map common synonyms to the same bucket
        $fats = ['fat', 'fats', 'oil', 'oils', 'fat & oil', 'fats & oils'];
        if (in_array($name, $fats, true)) {
            return 'fats_oils';
        }
        return $name;
    }
}
