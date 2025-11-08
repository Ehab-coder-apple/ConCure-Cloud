<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PatientImage extends Model
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
        'caption',
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function getUrlAttribute(): string
    {
        // Return a relative path so it always uses the current host (avoids SSL/domain mismatches)
        return '/storage/' . ltrim($this->path, '/');
    }

    public function isImage(): bool
    {
        return is_string($this->mime) && str_starts_with($this->mime, 'image/');
    }

    public function deleteWithFile(): void
    {
        if ($this->path && Storage::disk('public')->exists($this->path)) {
            Storage::disk('public')->delete($this->path);
        }
        $this->delete();
    }
}

