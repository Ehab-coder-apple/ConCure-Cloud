<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PatientFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'original_name',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'description',
        'uploaded_by',
    ];

    /**
     * File categories
     */
    const CATEGORIES = [
        'lab_result' => 'Lab Result',
        'medicine_photo' => 'Medicine Photo',
        'medical_report' => 'Medical Report',
        'other' => 'Other',
    ];

    /**
     * Get the patient that owns the file.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who uploaded this file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get the category display name.
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get the file extension.
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    /**
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        return in_array(strtolower($this->file_extension), $imageExtensions);
    }

    /**
     * Check if file is a PDF.
     */
    public function isPdf(): bool
    {
        return strtolower($this->file_extension) === 'pdf';
    }

    /**
     * Check if file is a document.
     */
    public function isDocument(): bool
    {
        $documentExtensions = ['doc', 'docx', 'txt', 'rtf'];
        return in_array(strtolower($this->file_extension), $documentExtensions);
    }

    /**
     * Get the file icon class.
     */
    public function getFileIconAttribute(): string
    {
        if ($this->isImage()) {
            return 'fas fa-image text-success';
        } elseif ($this->isPdf()) {
            return 'fas fa-file-pdf text-danger';
        } elseif ($this->isDocument()) {
            return 'fas fa-file-word text-primary';
        } else {
            return 'fas fa-file text-secondary';
        }
    }

    /**
     * Get the full file URL.
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Check if file exists on disk.
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Delete the file from storage.
     */
    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return Storage::delete($this->file_path);
        }
        return true;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            // Delete the actual file when the model is deleted
            $file->deleteFile();
        });
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by file type.
     */
    public function scopeByFileType($query, string $fileType)
    {
        return $query->where('file_type', $fileType);
    }

    /**
     * Scope to filter images.
     */
    public function scopeImages($query)
    {
        return $query->whereIn('file_type', ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp']);
    }

    /**
     * Scope to filter PDFs.
     */
    public function scopePdfs($query)
    {
        return $query->where('file_type', 'application/pdf');
    }

    /**
     * Scope to filter documents.
     */
    public function scopeDocuments($query)
    {
        return $query->whereIn('file_type', [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'application/rtf'
        ]);
    }

    /**
     * Scope to order by upload date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
