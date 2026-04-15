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
        'stock_quantity',
        'purchase_price',
        'selling_price',
        'expiry_date',
        'batch_number',
        'clinic_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_frequent' => 'boolean',
        'is_active' => 'boolean',
        'stock_quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'expiry_date' => 'date',
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

    /**
     * Scope to filter by creator.
     */
    public function scopeByCreator($query, int $creatorId)
    {
        return $query->where('created_by', $creatorId);
    }

    /**
     * Scope to filter medicines visible to a specific user based on role.
     * Regular users see: their own medicines + admin-uploaded medicines
     * Admins see: all medicines in their clinic
     */
    public function scopeVisibleToUser($query, User $user)
    {
        // Super Admins and Clinic Admins see all medicines in their clinic
        if ($user->isSuperAdmin() || $user->isClinicAdmin()) {
            return $query->where('clinic_id', $user->clinic_id);
        }

        // Regular users see their own medicines + medicines uploaded by admins
        return $query->where('clinic_id', $user->clinic_id)
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id) // Their own medicines
                  ->orWhereHas('creator', function ($creatorQuery) { // Or medicines uploaded by admins
                      $creatorQuery->where(function ($adminQuery) {
                          $adminQuery->where('role', 'super_admin')
                                    ->orWhere('role', 'admin');
                      });
                  });
            });
    }

    /**
     * Scope to filter low stock medicines.
     */
    public function scopeLowStock($query, int $threshold = 10)
    {
        return $query->where('stock_quantity', '<=', $threshold)
                    ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope to filter out of stock medicines.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope to filter expiring soon medicines.
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    /**
     * Scope to filter expired medicines.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now());
    }

    /**
     * Check if medicine is low on stock.
     */
    public function isLowStock(int $threshold = 10): bool
    {
        return $this->stock_quantity <= $threshold && $this->stock_quantity > 0;
    }

    /**
     * Check if medicine is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Check if medicine is expiring soon.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBetween(now(), now()->addDays($days));
    }

    /**
     * Check if medicine is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Get stock status badge color.
     */
    public function getStockStatusColorAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        } elseif ($this->isLowStock()) {
            return 'warning';
        }
        return 'success';
    }

    /**
     * Get stock status text.
     */
    public function getStockStatusTextAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        } elseif ($this->isLowStock()) {
            return 'Low Stock';
        }
        return 'In Stock';
    }
}
