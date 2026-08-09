<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
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
        'external_practitioner_name',
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
            $user = auth()->user();
            $tenantIds = auth()->check() ? $user?->accessibleTenantIds() : [];

            if ($user?->hasGlobalClinicAccess()) {
                return;
            }

            if ($tenantIds !== []) {
                $query->whereIn('tenant_id', $tenantIds);
            } else {
                $query->whereRaw('1 = 0'); // No tenant context = no data
            }
        });

        static::addGlobalScope('accessible_clinics', function (Builder $query) {
            if (!auth()->check()) {
                $query->whereRaw('1 = 0');
                return;
            }

            $user = auth()->user();
            if ($user?->hasGlobalClinicAccess()) {
                return;
            }

            $clinicIds = $user?->accessibleClinicIds() ?? [];
            if ($clinicIds === []) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->where(function (Builder $scope) use ($clinicIds) {
                $scope->whereHas('patient', fn (Builder $patientQuery) => $patientQuery->whereIn('clinic_id', $clinicIds))
                    ->orWhereHas('patientPackage.patient', fn (Builder $patientQuery) => $patientQuery->whereIn('clinic_id', $clinicIds));
            });
        });

        static::creating(function ($session) {
            $tenantId = $session->tenant_id;

            if (!$tenantId && $session->patient_id) {
                $tenantId = Patient::withoutGlobalScopes()
                    ->whereKey($session->patient_id)
                    ->join('clinics', 'patients.clinic_id', '=', 'clinics.id')
                    ->value('clinics.tenant_id');
            }

            if (!$tenantId && $session->patient_package_id) {
                $tenantId = PatientPackage::withoutGlobalScopes()
                    ->whereKey($session->patient_package_id)
                    ->join('patients', 'patient_packages.patient_id', '=', 'patients.id')
                    ->join('clinics', 'patients.clinic_id', '=', 'clinics.id')
                    ->value('clinics.tenant_id');
            }

            if (!$tenantId && auth()->check()) {
                $tenantIds = auth()->user()->accessibleTenantIds();
                if (count($tenantIds) === 1) {
                    $tenantId = $tenantIds[0];
                }
            }

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
     * Get the primary treatment (for direct treatment sessions, backward compat).
     */
    public function treatment(): BelongsTo
    {
        return $this->belongsTo(AestheticTreatment::class);
    }

    /**
     * Get all treatments selected for this session (multi-treatment support
     * for direct treatment sessions). Mirrors AestheticPackage::treatments().
     */
    public function treatments(): BelongsToMany
    {
        return $this->belongsToMany(AestheticTreatment::class, 'aesthetic_session_treatment', 'session_id', 'treatment_id');
    }

    /**
     * All treatments for this session, preferring the multi-select
     * `treatments` pivot and falling back to the legacy single `treatment`
     * relation for sessions created before multi-treatment support existed.
     */
    public function getEffectiveTreatmentsAttribute(): Collection
    {
        $pivotTreatments = $this->relationLoaded('treatments') ? $this->treatments : $this->treatments()->get();

        if ($pivotTreatments->isNotEmpty()) {
            return $pivotTreatments;
        }

        return $this->treatment ? collect([$this->treatment]) : collect();
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
        if ($this->effective_treatments->isNotEmpty()) {
            return $this->effective_treatments->pluck('name')->implode(', ');
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
        if ($this->assignedUser) {
            return $this->assignedUser->full_name;
        }

        if (!empty($this->external_practitioner_name)) {
            return $this->external_practitioner_name;
        }

        return __('Unassigned');
    }

    /**
     * Suggested next due date for follow-up (package or direct treatment).
     */
    public function getSuggestedNextDueDateAttribute()
    {
        if (!$this->session_date || (!$this->isPackageSession && !$this->isDirectSession)) {
            return null;
        }

        return $this->session_date->copy()->addDays(self::DEFAULT_PACKAGE_FOLLOW_UP_INTERVAL_DAYS);
    }

    /**
     * Whether this session still has a future slot to follow up on.
     *
     * Package sessions: true while the package has remaining/uncreated sessions.
     * Direct treatment sessions: always true, since there is no fixed session
     * quota — a follow-up can always be scheduled for a direct treatment.
     */
    public function getHasPendingFollowUpSlotAttribute(): bool
    {
        if ($this->isDirectSession) {
            return true;
        }

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
     *
     * Direct treatment sessions: open whenever the session is completed and a
     * next due date has been set (no package session quota to check).
     * Package sessions: also requires a remaining/uncreated slot in the
     * package and that no later package session has already been booked.
     */
    public function getHasOpenReminderAttribute(): bool
    {
        if ($this->status !== 'completed' || is_null($this->next_due_date)) {
            return false;
        }

        if ($this->isDirectSession) {
            return true;
        }

        return $this->isPackageSession
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
