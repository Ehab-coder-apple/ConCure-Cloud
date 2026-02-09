<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DentalToothRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dental_chart_id',
        'tooth_number',
        'conditions',
        'surfaces_affected',
        'notes',
        'primary_condition',
        'severity',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'conditions' => 'array',
        'surfaces_affected' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Dental conditions
     */
    const CONDITIONS = [
        'healthy' => ['name' => 'Healthy', 'color' => '#FFFFFF', 'icon' => '✓'],
        'caries' => ['name' => 'Caries (Cavity)', 'color' => '#FF0000', 'icon' => '🦷'],
        'filling' => ['name' => 'Filling (Restoration)', 'color' => '#0000FF', 'icon' => '🔵'],
        'crown' => ['name' => 'Crown', 'color' => '#FFD700', 'icon' => '👑'],
        'root_canal' => ['name' => 'Root Canal Treatment', 'color' => '#800080', 'icon' => '🔴'],
        'extraction' => ['name' => 'Extraction (Missing)', 'color' => '#808080', 'icon' => '✖'],
        'implant' => ['name' => 'Implant', 'color' => '#00FF00', 'icon' => '🔩'],
        'bridge' => ['name' => 'Bridge', 'color' => '#FFA500', 'icon' => '🌉'],
        'fracture' => ['name' => 'Fracture', 'color' => '#8B0000', 'icon' => '⚡'],
        'periodontal' => ['name' => 'Gingival/Periodontal Issue', 'color' => '#FFC0CB', 'icon' => '🩹'],
        'other' => ['name' => 'Other', 'color' => '#CCCCCC', 'icon' => '?'],
    ];

    /**
     * Tooth surfaces
     */
    const SURFACES = [
        'O' => 'Occlusal (Chewing surface)',
        'M' => 'Mesial (Toward midline)',
        'D' => 'Distal (Away from midline)',
        'B' => 'Buccal (Cheek side)',
        'F' => 'Facial (Cheek side)',
        'L' => 'Lingual (Tongue side)',
        'P' => 'Palatal (Tongue side)',
    ];

    /**
     * Severity levels
     */
    const SEVERITIES = [
        'mild' => 'Mild',
        'moderate' => 'Moderate',
        'severe' => 'Severe',
    ];

    /**
     * Get the dental chart that owns this tooth record.
     */
    public function dentalChart(): BelongsTo
    {
        return $this->belongsTo(DentalChart::class);
    }

    /**
     * Get the user who created this tooth record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this tooth record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the primary condition display name.
     */
    public function getPrimaryConditionDisplayAttribute(): string
    {
        return self::CONDITIONS[$this->primary_condition]['name'] ?? $this->primary_condition;
    }

    /**
     * Get the primary condition color.
     */
    public function getPrimaryConditionColorAttribute(): string
    {
        return self::CONDITIONS[$this->primary_condition]['color'] ?? '#FFFFFF';
    }

    /**
     * Get the primary condition icon.
     */
    public function getPrimaryConditionIconAttribute(): string
    {
        return self::CONDITIONS[$this->primary_condition]['icon'] ?? '';
    }

    /**
     * Get the severity display name.
     */
    public function getSeverityDisplayAttribute(): ?string
    {
        return $this->severity ? self::SEVERITIES[$this->severity] ?? $this->severity : null;
    }

    /**
     * Get surfaces as readable string.
     */
    public function getSurfacesDisplayAttribute(): string
    {
        if (!$this->surfaces_affected || empty($this->surfaces_affected)) {
            return 'All surfaces';
        }

        return implode(', ', array_map(function($surface) {
            return self::SURFACES[$surface] ?? $surface;
        }, $this->surfaces_affected));
    }

    /**
     * Get surfaces as short string (e.g., "MO", "DOB").
     */
    public function getSurfacesShortAttribute(): string
    {
        if (!$this->surfaces_affected || empty($this->surfaces_affected)) {
            return '';
        }

        return implode('', $this->surfaces_affected);
    }

    /**
     * Check if tooth has a specific condition.
     */
    public function hasCondition(string $condition): bool
    {
        return in_array($condition, $this->conditions ?? []);
    }
}

