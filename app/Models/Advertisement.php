<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'title_translations',
        'description',
        'description_translations',
        'image_path',
        'link_url',
        'type',
        'position',
        'start_date',
        'end_date',
        'is_active',
        'click_count',
        'view_count',
        'target_audience',
        'clinic_id',
        'created_by',
        'priority',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'description_translations' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'click_count' => 'integer',
        'view_count' => 'integer',
        'target_audience' => 'array',
        'priority' => 'integer',
    ];

    /**
     * Advertisement types
     */
    const TYPES = [
        'banner' => 'Banner',
        'popup' => 'Popup',
        'sidebar' => 'Sidebar',
        'footer' => 'Footer',
        'notification' => 'Notification',
    ];

    /**
     * Advertisement positions
     */
    const POSITIONS = [
        'top' => 'Top',
        'middle' => 'Middle',
        'bottom' => 'Bottom',
        'left' => 'Left Sidebar',
        'right' => 'Right Sidebar',
        'center' => 'Center',
    ];

    /**
     * Target audiences
     */
    const TARGET_AUDIENCES = [
        'all' => 'All Users',
        'patients' => 'Patients Only',
        'staff' => 'Staff Only',
        'doctors' => 'Doctors Only',
        'new_patients' => 'New Patients',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($advertisement) {
            // Delete image file when advertisement is deleted
            if ($advertisement->image_path && Storage::exists($advertisement->image_path)) {
                Storage::delete($advertisement->image_path);
            }
        });
    }

    /**
     * Get the clinic that owns the advertisement.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this advertisement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the translated title for current locale.
     */
    public function getTranslatedTitleAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->title_translations[$locale] ?? $this->title;
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
     * Get the type display name.
     */
    public function getTypeDisplayAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get the position display name.
     */
    public function getPositionDisplayAttribute(): string
    {
        return self::POSITIONS[$this->position] ?? $this->position;
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    /**
     * Check if image file exists.
     */
    public function hasImage(): bool
    {
        return $this->image_path && Storage::exists($this->image_path);
    }

    /**
     * Check if advertisement is currently active and within date range.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $now->isBefore($this->start_date)) {
            return false;
        }
        
        if ($this->end_date && $now->isAfter($this->end_date)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if advertisement is expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date && now()->isAfter($this->end_date);
    }

    /**
     * Check if advertisement targets specific audience.
     */
    public function targetsAudience(string $audience): bool
    {
        if (!$this->target_audience || in_array('all', $this->target_audience)) {
            return true;
        }
        
        return in_array($audience, $this->target_audience);
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Increment click count.
     */
    public function incrementClicks(): void
    {
        $this->increment('click_count');
    }

    /**
     * Get click-through rate.
     */
    public function getClickThroughRateAttribute(): float
    {
        if ($this->view_count === 0) {
            return 0;
        }
        
        return round(($this->click_count / $this->view_count) * 100, 2);
    }

    /**
     * Set title translation for a specific locale.
     */
    public function setTitleTranslation(string $locale, string $title): void
    {
        $translations = $this->title_translations ?? [];
        $translations[$locale] = $title;
        $this->title_translations = $translations;
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
     * Scope to filter active advertisements.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter currently active advertisements (within date range).
     */
    public function scopeCurrentlyActive($query)
    {
        $now = now();
        return $query->active()
                    ->where(function ($q) use ($now) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $now);
                    });
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by position.
     */
    public function scopeByPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by target audience.
     */
    public function scopeForAudience($query, string $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->whereNull('target_audience')
              ->orWhereJsonContains('target_audience', 'all')
              ->orWhereJsonContains('target_audience', $audience);
        });
    }

    /**
     * Scope to filter expired advertisements.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('end_date')
                    ->where('end_date', '<', now());
    }

    /**
     * Scope to order by priority and creation date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Scope to search advertisements.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereRaw("JSON_EXTRACT(title_translations, '$.en') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(title_translations, '$.ar') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(title_translations, '$.ku') LIKE ?", ["%{$search}%"]);
        });
    }
}
