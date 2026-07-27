<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Services\StorageQuotaService;

class SessionImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'tenant_id',
        'type',
        'image_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    const TYPES = [
        'before' => 'Before',
        'after' => 'After',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (\Illuminate\Database\Eloquent\Builder $query) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            if ($tenantId) {
                $query->where('session_images.tenant_id', $tenantId);
            } else {
                $query->whereRaw('1 = 0'); // No tenant context = no data
            }
        });

        static::creating(function ($image) {
            if (empty($image->tenant_id) && $image->session) {
                $image->tenant_id = $image->session->tenant_id;
            }
        });
    }

    /**
     * Get the session this image belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AestheticSession::class);
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): string
    {
        return StorageQuotaService::getSecureUrl($this->image_path);
    }

    /**
     * Check if file exists on disk.
     */
    public function fileExists(): bool
    {
        return StorageQuotaService::fileExistsOnDisk($this->image_path);
    }

    /**
     * Delete the file from storage.
     */
    public function deleteFile(): bool
    {
        return StorageQuotaService::deleteFromDisk($this->image_path);
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            $image->deleteFile();
        });
    }
}
