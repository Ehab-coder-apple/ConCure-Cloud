<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AestheticAftercareTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'title',
        'instructions',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;

            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            } else {
                $query->whereRaw('1 = 0');
            }
        });

        static::creating(function (self $template): void {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;

            if ($tenantId && empty($template->tenant_id)) {
                $template->tenant_id = $tenantId;
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(AestheticAftercareIssue::class, 'aftercare_template_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getCategoryDisplayAttribute(): string
    {
        return AestheticTreatment::CATEGORIES[$this->category] ?? ucwords(str_replace(['_', '-'], ' ', $this->category));
    }
}