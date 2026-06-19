<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class AestheticSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'patient_package_id',
        'patient_id',
        'treatment_id',
        'assigned_user_id',
        'session_number',
        'session_date',
        'next_due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'session_number' => 'integer',
        'session_date' => 'date',
        'next_due_date' => 'date',
    ];

    public const DEFAULT_PACKAGE_FOLLOW_UP_INTERVAL_DAYS = 28;

    const STATUSES = [
        'scheduled' => 'Scheduled',
        'started' => 'Started',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
    ];

    const STATUS_COLORS = [
        'scheduled' => 'warning',
        'started' => 'primary',
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
            } else {
                $query->whereRaw('1 = 0'); // No tenant context = no data
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
     * Get the user assigned to run this session.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
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
     * Resolve the patient for either package or direct sessions.
     */
    public function getResolvedPatientAttribute(): ?Patient
    {
        return $this->patient ?: $this->patientPackage?->patient;
    }

    /**
     * Get a display-friendly patient name for this session.
     */
    public function getPatientDisplayAttribute(): string
    {
        return $this->resolvedPatient?->full_name ?? __('Unknown Patient');
    }

    /**
     * Display-friendly assigned person label.
     */
    public function getAssignedPersonDisplayAttribute(): string
    {
        return $this->assignedUser?->full_name ?? __('Unassigned');
    }

    /**
     * Suggested next due date for package follow-up.
     */
    public function getSuggestedNextDueDateAttribute()
    {
        return $this->isPackageSession && $this->session_date
            ? $this->session_date->copy()->addDays(self::DEFAULT_PACKAGE_FOLLOW_UP_INTERVAL_DAYS)
            : null;
    }

    /**
     * Whether this package session still has a future slot to follow up on.
     */
    public function getHasPendingFollowUpSlotAttribute(): bool
    {
        if (!$this->isPackageSession || !$this->patientPackage) {
            return false;
        }

        if ($this->patientPackage->sessions_remaining > 0) {
            return true;
        }

        return $this->session_number < $this->patientPackage->total_sessions;
    }

    /**
     * Whether a later session already exists in this package.
     */
    public function getHasSubsequentPackageSessionAttribute(): bool
    {
        if (!$this->isPackageSession || !$this->patient_package_id) {
            return false;
        }

        return self::query()
            ->where('patient_package_id', $this->patient_package_id)
            ->where('session_number', '>', $this->session_number)
            ->exists();
    }

    /**
     * Whether this session currently represents an open follow-up reminder.
     */
    public function getHasOpenReminderAttribute(): bool
    {
        return $this->status === 'completed'
            && $this->isPackageSession
            && !is_null($this->next_due_date)
            && $this->has_pending_follow_up_slot
            && !$this->has_subsequent_package_session;
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
     * Get consent forms linked to this session.
     */
    public function consentForms(): HasMany
    {
        return $this->hasMany(ConsentForm::class, 'session_id');
    }

    /**
     * Get aftercare issues linked to this session.
     */
    public function aftercareIssues(): HasMany
    {
        return $this->hasMany(AestheticAftercareIssue::class, 'session_id');
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
