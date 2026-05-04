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
