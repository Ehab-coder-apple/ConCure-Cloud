<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'generic_name',
        'brand_name',
        'dosage',
        'form',
        'description',
        'side_effects',
        'contraindications',
        'is_frequent',
        'clinic_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_frequent' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Medicine forms
     */
    const FORMS = [
        'tablet' => 'Tablet',
        'capsule' => 'Capsule',
        'syrup' => 'Syrup',
        'injection' => 'Injection',
        'cream' => 'Cream',
        'ointment' => 'Ointment',
        'drops' => 'Drops',
        'inhaler' => 'Inhaler',
        'patch' => 'Patch',
        'suppository' => 'Suppository',
        'other' => 'Other',
    ];

    /**
     * Get the clinic that owns the medicine.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this medicine.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the prescription medicines for this medicine.
     */
    public function prescriptionMedicines(): HasMany
    {
        return $this->hasMany(PrescriptionMedicine::class);
    }

    /**
     * Get the form display name.
     */
    public function getFormDisplayAttribute(): string
    {
        return self::FORMS[$this->form] ?? $this->form;
    }

    /**
     * Get the full medicine name with dosage.
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->name;
        if ($this->dosage) {
            $name .= ' ' . $this->dosage;
        }
        if ($this->form) {
            $name .= ' (' . $this->form_display . ')';
        }
        return $name;
    }

    /**
     * Scope to filter active medicines.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter frequent medicines.
     */
    public function scopeFrequent($query)
    {
        return $query->where('is_frequent', true);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, ?int $clinicId)
    {
        if ($clinicId === null) {
            // If no clinic ID provided, return empty result set for security
            return $query->whereRaw('1 = 0');
        }

        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to search medicines.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('generic_name', 'like', "%{$search}%")
              ->orWhere('brand_name', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by form.
     */
    public function scopeByForm($query, string $form)
    {
        return $query->where('form', $form);
    }
}
