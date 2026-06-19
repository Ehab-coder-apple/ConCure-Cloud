<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'package_id',
        'sessions_used',
        'sessions_remaining',
        'purchase_date',
    ];

    protected $casts = [
        'sessions_used' => 'integer',
        'sessions_remaining' => 'integer',
        'purchase_date' => 'date',
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

            $query->whereHas('patient', fn (Builder $patientQuery) => $patientQuery->whereIn('clinic_id', $clinicIds));
        });

        static::creating(function ($package) {
            $tenantId = $package->tenant_id;

            if (!$tenantId && $package->patient_id) {
                $tenantId = Patient::withoutGlobalScopes()
                    ->whereKey($package->patient_id)
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
                $package->tenant_id = $tenantId;
            }
        });
    }

    /**
     * Get the patient this package was assigned to.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the package that was purchased.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(AestheticPackage::class)->withTrashed();
    }

    /**
     * Get the sessions for this patient package.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(AestheticSession::class, 'patient_package_id')->orderBy('session_number');
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter active packages (has remaining sessions).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('sessions_remaining', '>', 0);
    }

    /**
     * Scope to filter completed packages (no remaining sessions).
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('sessions_remaining', '<=', 0);
    }

    /**
     * Use one session from the package.
     */
    public function useSession(): bool
    {
        if ($this->sessions_remaining <= 0) {
            return false;
        }

        $this->decrement('sessions_remaining');
        $this->increment('sessions_used');

        return true;
    }

    /**
     * Check if the package has remaining sessions.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->sessions_remaining > 0;
    }

    /**
     * Get usage percentage.
     */
    public function getUsagePercentageAttribute(): float
    {
        $total = $this->sessions_used + $this->sessions_remaining;
        if ($total <= 0) {
            return 0.0;
        }

        return round(($this->sessions_used / $total) * 100, 1);
    }

    /**
     * Total sessions expected for this package assignment.
     */
    public function getTotalSessionsAttribute(): int
    {
        return (int) ($this->package?->total_sessions ?? ($this->sessions_used + $this->sessions_remaining));
    }

    /**
     * Backward-compatible remaining sessions alias.
     */
    public function getRemainingSessionsAttribute(): int
    {
        return (int) $this->sessions_remaining;
    }
}
