<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalLab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lab_type',
        'address',
        'phone',
        'whatsapp',
        'email',
        'website',
        'notes',
        'is_active',
        'sort_order',
        'clinic_id',
        'created_by',
        // Dental-specific fields
        'dental_specialties',
        'turnaround_days',
        'accepts_digital_impressions',
        'equipment_capabilities',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'dental_specialties' => 'array',
        'turnaround_days' => 'integer',
        'accepts_digital_impressions' => 'boolean',
    ];

    /**
     * Lab type constants
     */
    const TYPE_MEDICAL = 'medical';
    const TYPE_DENTAL = 'dental';

    /**
     * Get the clinic that owns this external lab.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this external lab.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get dental lab requests for this lab.
     */
    public function dentalLabRequests(): HasMany
    {
        return $this->hasMany(DentalLabRequest::class);
    }

    /**
     * Scope to filter active labs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter medical labs.
     */
    public function scopeMedical($query)
    {
        return $query->where('lab_type', self::TYPE_MEDICAL);
    }

    /**
     * Scope to filter dental labs.
     */
    public function scopeDental($query)
    {
        return $query->where('lab_type', self::TYPE_DENTAL);
    }

    /**
     * Scope to filter by lab type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('lab_type', $type);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the display name with contact info.
     */
    public function getDisplayNameAttribute(): string
    {
        $display = $this->name;
        if ($this->phone) {
            $display .= ' - ' . $this->phone;
        }
        return $display;
    }
}
