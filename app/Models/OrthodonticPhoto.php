<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class OrthodonticPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orthodontic_case_id',
        'patient_id',
        'clinic_id',
        'orthodontic_visit_id',
        'photo_type',
        'view_type',
        'stage',
        'photo_date',
        'file_path',
        'file_name',
        'file_size',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'photo_date' => 'date',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const PHOTO_TYPES = [
        'intraoral' => 'Intraoral',
        'extraoral' => 'Extraoral',
        'xray' => 'X-Ray',
        'scan' => '3D Scan',
    ];

    const VIEW_TYPES = [
        'frontal' => 'Frontal',
        'lateral_right' => 'Lateral Right',
        'lateral_left' => 'Lateral Left',
        'occlusal_upper' => 'Occlusal Upper',
        'occlusal_lower' => 'Occlusal Lower',
        'smile' => 'Smile',
        'profile' => 'Profile',
        'other' => 'Other',
    ];

    const STAGES = [
        'before' => 'Before Treatment',
        'during' => 'During Treatment',
        'after' => 'After Treatment',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($photo) {
            // Delete the actual file when the model is deleted
            if ($photo->file_path && Storage::exists($photo->file_path)) {
                Storage::delete($photo->file_path);
            }
        });
    }

    // Relationships

    public function orthodonticCase(): BelongsTo
    {
        return $this->belongsTo(OrthodonticCase::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(OrthodonticVisit::class, 'orthodontic_visit_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessors

    public function getPhotoTypeDisplayAttribute(): string
    {
        return self::PHOTO_TYPES[$this->photo_type] ?? $this->photo_type;
    }

    public function getViewTypeDisplayAttribute(): string
    {
        return self::VIEW_TYPES[$this->view_type] ?? $this->view_type;
    }

    public function getStageDisplayAttribute(): string
    {
        return self::STAGES[$this->stage] ?? $this->stage;
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
