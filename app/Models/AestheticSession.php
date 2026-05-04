<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AestheticSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'patient_package_id',
        'patient_id',
        'treatment_id',
        'session_number',
        'session_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'session_number' => 'integer',
        'session_date' => 'date',
    ];

    const STATUSES = [
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
    ];

    const STATUS_COLORS = [
        'scheduled' => 'warning',
        'completed' => 'success',
        'cancelled' => 'secondary',
        'no_show' => 'danger',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }
        });

        static::creating(function ($session) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;
            if ($tenantId) {
                $session->tenant_id = $tenantId;
            }
        });

        static::deleting(function ($session) {
            // Restore stock for completed sessions when deleting
            if ($session->status === 'completed') {
                foreach ($session->inventoryUsages as $usage) {
                    $usage->product->addStock($usage->quantity_used);
                }
            }
            $session->inventoryUsages()->delete();
            $session->images()->each(function ($image) {
                $image->delete();
            });
        });
    }

    /**
     * Get the patient package this session belongs to (for package sessions).
     */
    public function patientPackage(): BelongsTo
    {
        return $this->belongsTo(PatientPackage::class);
    }

    /**
     * Get the patient (for direct treatment sessions).
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the treatment (for direct treatment sessions).
     */
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(AestheticTreatment::class);
    }

    /**
     * Check if this is a package-based session.
     */
    public function getIsPackageSessionAttribute(): bool
    {
        return !is_null($this->patient_package_id);
    }

    /**
     * Check if this is a direct treatment session.
     */
    public function getIsDirectSessionAttribute(): bool
    {
        return is_null($this->patient_package_id) && !is_null($this->patient_id);
    }

    /**
     * Get display name for the session context (package or treatment).
     */
    public function getSessionContextAttribute(): string
    {
        if ($this->isPackageSession) {
            return $this->patientPackage?->package?->name ?? __('Package');
        }
        if ($this->treatment) {
            return $this->treatment->name;
        }
        return __('Direct Treatment');
    }

    /**
     * Get the images for this session.
     */
    public function images(): HasMany
    {
        return $this->hasMany(SessionImage::class, 'session_id');
    }

    /**
     * Get before images for this session.
     */
    public function beforeImages(): HasMany
    {
        return $this->hasMany(SessionImage::class, 'session_id')->where('type', 'before');
    }

    /**
     * Get after images for this session.
     */
    public function afterImages(): HasMany
    {
        return $this->hasMany(SessionImage::class, 'session_id')->where('type', 'after');
    }

    /**
     * Get the inventory usages for this session.
     */
    public function inventoryUsages(): HasMany
    {
        return $this->hasMany(SessionInventoryUsage::class, 'session_id');
    }

    /**
     * Scope by patient package.
     */
    public function scopeByPatientPackage(Builder $query, int $patientPackageId): Builder
    {
        return $query->where('patient_package_id', $patientPackageId);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Check if session has both before and after images.
     */
    public function getHasComparisonAttribute(): bool
    {
        return $this->beforeImages()->count() > 0 && $this->afterImages()->count() > 0;
    }
}
