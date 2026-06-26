<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'speciality',
        'city',
        'area',
        'street',
        'logo',
        'settings',
        'is_active',
        'is_demo',
        'can_export',
        'enabled_modules',
        'max_users',
        'activated_at',
        'activation_code',
        'plan_id',
        'billing_cycle',
        'next_billing_at',
        'custom_monthly_price',
        'custom_yearly_price',
        'billing_user_price',
        'billing_user_count',
        'service_charge_amount',
        'service_charge_date',
        'service_charge_note',
        'contract_renewal_at',
        'storage_limit',
        'storage_used',
        'country_id',
        'schedule_override_id',
        'tenant_id',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_demo' => 'boolean',
        'can_export' => 'boolean',
        'enabled_modules' => 'array',
        'activated_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'custom_monthly_price' => 'decimal:2',
        'custom_yearly_price' => 'decimal:2',
        'billing_user_price' => 'decimal:2',
        'service_charge_amount' => 'decimal:2',
        'service_charge_date' => 'date',
        'contract_renewal_at' => 'date',
        'storage_limit' => 'integer',
        'storage_used' => 'integer',
    ];

    /**
     * The subscription plan this clinic is assigned to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * The country this clinic belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * The vaccination schedule override for this clinic.
     */
    public function scheduleOverride(): BelongsTo
    {
        return $this->belongsTo(VaccinationSchedule::class, 'schedule_override_id');
    }

    /**
     * Get the effective vaccination schedule for this clinic.
     * Clinic override > Country default.
     */
    public function getEffectiveVaccinationScheduleAttribute(): ?VaccinationSchedule
    {
        if ($this->schedule_override_id) {
            return $this->scheduleOverride;
        }
        if ($this->country_id) {
            return $this->country?->default_schedule;
        }
        return null;
    }

    /**
     * Get the users for the clinic.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scoped Super Admins assigned to this clinic.
     */
    public function superAdmins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clinic_super_admin')
            ->withTimestamps();
    }

    /**
     * Get the patients for the clinic.
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Get the contracts for the clinic.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(ClinicContract::class);
    }

    /**
     * Get the active (pending or accepted) contract for the clinic.
     */
    public function activeContract()
    {
        return $this->hasOne(ClinicContract::class)
            ->whereIn('status', ['pending', 'accepted'])
            ->latest();
    }

    /**
     * Get the medicines for the clinic.
     */
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }

    /**
     * Get the prescriptions for the clinic.
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the appointments for the clinic.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the lab tests for the clinic.
     */
    public function labTests(): HasMany
    {
        return $this->hasMany(LabTest::class);
    }

    /**
     * Get the invoices for the clinic.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the aesthetic invoices for the clinic.
     */
    public function aestheticInvoices(): HasMany
    {
        return $this->hasMany(AestheticInvoice::class, 'clinic_id');
    }

    /**
     * Get the expenses for the clinic.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the advertisements for the clinic.
     */
    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class);
    }

    /**
     * Get patient files through patients (for storage calculation).
     */
    public function patientFiles(): HasManyThrough
    {
        return $this->hasManyThrough(PatientFile::class, Patient::class);
    }

    /**
     * Get the audit logs for the clinic.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the activation codes for the clinic.
     */
    public function activationCodes(): HasMany
    {
        return $this->hasMany(ActivationCode::class);
    }

    /**
     * Get the settings for the clinic.
     */
    public function clinicSettings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    /**
     * Get the communication logs for the clinic.
     */
    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    /**
     * Get the notification settings for the clinic.
     */
    public function notificationSettings(): HasOne
    {
        return $this->hasOne(NotificationSetting::class);
    }

    /**
     * Get notification logs for the clinic.
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Get scheduled notifications for the clinic.
     */
    public function scheduledNotifications(): HasMany
    {
        return $this->hasMany(ScheduledNotification::class);
    }

    /**
     * Check if clinic is active.
     */
    public function isActiveWithValidSubscription(): bool
    {
        return $this->is_active && $this->activated_at !== null;
    }

    // Subscription and trial methods removed - no longer needed

    /**
     * Check if clinic has reached user limit.
     */
    public function hasReachedUserLimit(): bool
    {
        return $this->users()->active()->count() >= $this->max_users;
    }

    /**
     * Get remaining user slots.
     */
    public function getRemainingUserSlots(): int
    {
        return max(0, $this->max_users - $this->users()->active()->count());
    }

    /**
     * Get user limit information.
     */
    public function getUserLimitInfo(): array
    {
        $activeUsers = $this->users()->active()->count();
        return [
            'current_users' => $activeUsers,
            'max_users' => $this->max_users,
            'remaining_slots' => $this->getRemainingUserSlots(),
            'has_reached_limit' => $this->hasReachedUserLimit(),
        ];
    }

    /**
     * Get clinic setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Set clinic setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Ensure the clinic has a tenant identifier and persist it if missing.
     */
    public function ensureTenantId(): ?string
    {
        if (!$this->exists) {
            return $this->tenant_id;
        }

        if (!empty($this->tenant_id)) {
            return $this->tenant_id;
        }

        $this->tenant_id = 'TEN-' . $this->id;
        $this->save();

        return $this->tenant_id;
    }

    /**
     * Display-friendly address using the structured fields when present.
     * Falls back to legacy `address`.
     */
    public function getFormattedAddressAttribute(): ?string
    {
        $parts = collect([
            $this->street,
            $this->area,
            $this->city,
        ])
            ->map(fn ($v) => is_string($v) ? trim($v) : $v)
            ->filter(fn ($v) => !empty($v));

        if ($parts->isNotEmpty()) {
            return $parts->implode(', ');
        }

        return $this->address;
    }

    /**
     * Module groups for organized display in the UI.
     * Each group has an icon, label, and list of modules.
     */
    public const MODULE_GROUPS = [
        'core' => [
            'label' => 'Core',
            'icon'  => 'fas fa-home',
            'modules' => [
                'dashboard'     => 'Dashboard',
                'patients'      => 'Patient Management',
                'appointments'  => 'Appointments',
                'prescriptions' => 'Prescriptions',
                'messages'      => 'Messages',
            ],
        ],
        'clinical' => [
            'label' => 'Clinical',
            'icon'  => 'fas fa-stethoscope',
            'modules' => [
                'ai_assistant'  => 'AI Medical Assistant',
                'lab'           => 'Laboratory',
                'radiology'     => 'Radiology',
                'image_bank'    => 'Medical Image Bank',
                'forms'         => 'Forms',
            ],
        ],
        'modules' => [
            'label' => 'Modules',
            'icon'  => 'fas fa-puzzle-piece',
            'modules' => [
                'dental'        => 'Dental Module',
                'orthodontics'  => 'Orthodontic Module',
                'ent'           => 'ENT Module',
                'surgery'       => 'Surgical Module',
                'aesthetic'     => 'Aesthetic Module',
                'nutrition'     => 'Nutrition & Diet Planning',
                'food_database' => 'Food Database',
                'pediatric'     => 'Pediatric Module',
                'vaccination'   => 'Vaccination Management',
            ],
        ],
        'operations' => [
            'label' => 'Operations',
            'icon'  => 'fas fa-cogs',
            'modules' => [
                'medicines'     => 'Medicine Inventory',
                'finance'       => 'Financial Management',
            ],
        ],
        'system' => [
            'label' => 'System',
            'icon'  => 'fas fa-shield-alt',
            'modules' => [
                'whatsapp'      => 'WhatsApp Messaging',
            ],
        ],
    ];

    /**
     * Available modules that can be toggled per clinic.
     * Flat list derived from MODULE_GROUPS for backward compatibility.
     */
    public const AVAILABLE_MODULES = [
        'dashboard'     => 'Dashboard',
        'patients'      => 'Patient Management',
        'appointments'  => 'Appointments',
        'prescriptions' => 'Prescriptions',
        'messages'      => 'Messages',
        'ai_assistant'  => 'AI Medical Assistant',
        'lab'           => 'Laboratory',
        'radiology'     => 'Radiology',
        'image_bank'    => 'Medical Image Bank',
        'forms'         => 'Forms',
        'surgery'       => 'Surgical Module',
        'dental'        => 'Dental Module',
        'orthodontics'  => 'Orthodontics Module',
        'ent'           => 'ENT Module',
        'aesthetic'     => 'Aesthetic Module',
        'nutrition'     => 'Nutrition & Diet Planning',
        'food_database' => 'Food Database',
        'pediatric'     => 'Pediatric Module',
        'vaccination'   => 'Vaccination Management',
        'medicines'     => 'Medicine Inventory',
        'finance'       => 'Financial Management',
        'whatsapp'      => 'WhatsApp Messaging',
    ];

    /**
     * Modules that are enabled by default for all clinics.
     * Super admin can explicitly disable them via clinic settings.
     */
    public const DEFAULT_ENABLED_MODULES = [
        'dashboard',
        'patients',
        'appointments',
        'prescriptions',
        'messages',
        'ent', // ENT module enabled by default
        'dental',
        'orthodontics', // Orthodontics module enabled by default
        'aesthetic',
        'pediatric',
        'nutrition',
        'lab',
        'radiology',
        'medicines',
        'finance',
    ];

    /**
     * Check if a specific module is enabled for this clinic.
     * If enabled_modules is null (not yet configured), all modules are enabled by default.
     * Some modules are enabled by default and must be explicitly disabled.
     */
    public function hasModule(string $module): bool
    {
        // If not configured yet, allow everything (backward compatible)
        if ($this->enabled_modules === null) {
            return true;
        }

        // If enabled_modules is an empty array, use defaults
        if (is_array($this->enabled_modules) && empty($this->enabled_modules)) {
            return in_array($module, self::DEFAULT_ENABLED_MODULES);
        }

        return in_array($module, $this->enabled_modules);
    }

    /**
     * Scope to filter active clinics.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter activated clinics.
     */
    public function scopeActivated($query)
    {
        return $query->whereNotNull('activated_at');
    }

    // Subscription scope removed - no longer needed

    /**
     * Get storage info for this clinic.
     */
    public function getStorageInfoAttribute(): array
    {
        return app(\App\Services\StorageQuotaService::class)->getStorageInfo($this->id);
    }

    /**
     * Check if clinic has available storage space for a given file size (bytes).
     */
    public function hasStorageSpace(int $fileSize = 0): bool
    {
        return app(\App\Services\StorageQuotaService::class)->canUpload($this->id, $fileSize);
    }

    /**
     * Get storage usage percentage.
     */
    public function getStoragePercentageAttribute(): float
    {
        $limit = $this->storage_limit ?: \App\Services\StorageQuotaService::DEFAULT_LIMIT;
        $used = app(\App\Services\StorageQuotaService::class)->getStorageUsed($this->id);
        return $limit > 0 ? round(($used / $limit) * 100, 2) : 0;
    }

    /**
     * Check if this clinic is allowed to export data.
     * Demo clinics require explicit can_export = true (set by master admin).
     * Regular clinics can always export.
     */
    public function canExportData(): bool
    {
        // Demo clinics need explicit permission
        if ($this->is_demo) {
            return (bool) $this->can_export;
        }

        // Regular clinics can export by default
        return true;
    }

    /**
     * Scope to filter demo clinics.
     */
    public function scopeDemo($query)
    {
        return $query->where('is_demo', true);
    }

    /**
     * Scope to filter by tenant.
     */
    public function scopeByTenant($query, ?string $tenantId)
    {
        if ($tenantId === null) {
            return $query;
        }

        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Get all clinics in the same tenant.
     */
    public function getTenantClinics()
    {
        if (!$this->tenant_id) {
            return collect([$this]);
        }

        return static::where('tenant_id', $this->tenant_id)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get IDs of all clinics in the same tenant.
     */
    public function getTenantClinicIds(): array
    {
        if (!$this->tenant_id) {
            return [$this->id];
        }

        return static::where('tenant_id', $this->tenant_id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
    }
}
