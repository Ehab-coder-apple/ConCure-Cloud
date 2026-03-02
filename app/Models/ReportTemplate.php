<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'created_by',
        'name',
        'title',
        'content',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Available icons for templates.
     */
    public const ICONS = [
        'fas fa-file-alt' => 'Document',
        'fas fa-bed' => 'Bed / Sick Leave',
        'fas fa-heartbeat' => 'Heartbeat / Fitness',
        'fas fa-calendar-check' => 'Calendar / Follow-up',
        'fas fa-share' => 'Share / Referral',
        'fas fa-stethoscope' => 'Stethoscope',
        'fas fa-notes-medical' => 'Medical Notes',
        'fas fa-prescription' => 'Prescription',
        'fas fa-user-md' => 'Doctor',
        'fas fa-hospital' => 'Hospital',
        'fas fa-clipboard-check' => 'Clipboard Check',
        'fas fa-file-medical' => 'Medical File',
        'fas fa-tooth' => 'Tooth / Dental',
        'fas fa-eye' => 'Eye',
        'fas fa-baby' => 'Baby / Pediatric',
        'fas fa-bone' => 'Bone / Orthopedic',
    ];

    /**
     * Get the clinic that owns the template.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

