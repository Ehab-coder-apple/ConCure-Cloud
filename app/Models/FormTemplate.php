<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class FormTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'file_path',
        'original_filename',
        'file_type',
        'file_size',
        'category',
        'is_active',
        'clinic_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function patientForms(): HasMany
    {
        return $this->hasMany(PatientForm::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForClinic($query, ?int $clinicId)
    {
        if ($clinicId === null) {
            return $query->whereRaw('1 = 0');
        }
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) { return $query; }
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('category', 'like', "%{$term}%");
        });
    }

    public function scopeByCreator($query, int $creatorId)
    {
        return $query->where('created_by', $creatorId);
    }

    // Helpers
    public static function storageBaseDir(): string
    {
        return 'form_templates';
    }

    public static function storageDirForClinic(int $clinicId): string
    {
        return self::storageBaseDir() . '/' . $clinicId;
    }

    public function getDownloadUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function getExtensionAttribute(): ?string
    {
        if ($this->original_filename) {
            return strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));
        }
        if ($this->file_path) {
            return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        }
        return null;
    }

    public static function allowedExtensions(): array
    {
        return ['doc', 'docx', 'xls', 'xlsx'];
    }
}

