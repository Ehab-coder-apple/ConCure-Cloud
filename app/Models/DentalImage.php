<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DentalImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'dental_chart_id',
        'dental_treatment_id',
        'tooth_number',
        'tooth_numbers',
        'image_type',
        'file_path',
        'filename',
        'mime_type',
        'file_size',
        'title',
        'description',
        'image_date',
        'uploaded_by',
    ];

    protected $casts = [
        'tooth_numbers' => 'array',
        'image_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['url'];

    /**
     * Image types
     */
    const IMAGE_TYPES = [
        'panoramic' => 'Panoramic X-Ray',
        'periapical' => 'Periapical X-Ray',
        'bitewing' => 'Bitewing X-Ray',
        'occlusal' => 'Occlusal X-Ray',
        'cephalometric' => 'Cephalometric X-Ray',
        'intraoral_photo' => 'Intraoral Photo',
        'extraoral_photo' => 'Extraoral Photo',
        'cbct' => 'CBCT Scan',
        'other' => 'Other',
    ];

    /**
     * Get the patient that owns this image.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the clinic that owns this image.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the dental chart associated with this image.
     */
    public function dentalChart(): BelongsTo
    {
        return $this->belongsTo(DentalChart::class);
    }

    /**
     * Get the dental treatment associated with this image.
     */
    public function dentalTreatment(): BelongsTo
    {
        return $this->belongsTo(DentalTreatment::class);
    }

    /**
     * Get the user who uploaded this image.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the image URL.
     */
    public function getUrlAttribute(): string
    {
        // Return a relative path so it always uses the current host
        return '/storage/' . ltrim($this->file_path, '/');
    }

    /**
     * Get the image type display name.
     */
    public function getImageTypeDisplayAttribute(): string
    {
        return self::IMAGE_TYPES[$this->image_type] ?? $this->image_type;
    }

    /**
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        return is_string($this->mime_type) && str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is DICOM.
     */
    public function isDicom(): bool
    {
        return is_string($this->mime_type) && 
               (str_contains($this->mime_type, 'dicom') || 
                str_ends_with($this->filename, '.dcm'));
    }

    /**
     * Get file size in human-readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by image type.
     */
    public function scopeByImageType($query, string $imageType)
    {
        return $query->where('image_type', $imageType);
    }
}

