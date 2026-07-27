<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\StorageQuotaService;

class PatientVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'uploaded_by_user_id',
        'path',
        'filename',
        'mime',
        'size',
        'title',
        'description',
        'condition_tags',
    ];

    protected $casts = [
        'condition_tags' => 'array',
    ];

    protected $appends = ['url'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function getUrlAttribute(): string
    {
        return StorageQuotaService::getSecureUrl($this->path);
    }

    public function isVideo(): bool
    {
        return is_string($this->mime) && str_starts_with($this->mime, 'video/');
    }

    /**
     * Get human-readable file size.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->size ?? 0;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function deleteWithFile(): void
    {
        StorageQuotaService::deleteFromDisk($this->path);
        $this->delete();
    }
}

