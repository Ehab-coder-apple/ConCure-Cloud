<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_demo' => 'boolean',
        'enabled_modules' => 'array',
        'activated_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'custom_monthly_price' => 'decimal:2',
        'custom_yearly_price' => 'decimal:2',
        'billing_user_price' => 'decimal:2',
        'service_charge_amount' => 'decimal:2',
        'service_charge_date' => 'date',
    ];

    /**
     * The subscription plan this clinic is assigned to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Get the users for the clinic.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the patients for the clinic.
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
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
     * Available modules that can be toggled per clinic.
     */
    public const AVAILABLE_MODULES = [
        'dashboard'     => 'Dashboard',
        'patients'      => 'Patient Management',
        'prescriptions' => 'Prescriptions',
        'appointments'  => 'Appointments',
        'medicines'     => 'Medicine Inventory',
        'nutrition'     => 'Nutrition Plans',
        'food_database' => 'Food Database',
        'forms'         => 'Forms',
        'lab'           => 'Laboratory',
        'radiology'     => 'Radiology',
        'dental'        => 'Dental Module',
        'finance'       => 'Financial Management',
        'ai_assistant'  => 'AI Medical Assistant',
        'image_bank'    => 'Medical Image Bank',
        'messages'      => 'Messages',
    ];

    /**
     * Check if a specific module is enabled for this clinic.
     * If enabled_modules is null (not yet configured), all modules are enabled by default.
     */
    public function hasModule(string $module): bool
    {
        // If not configured yet, allow everything (backward compatible)
        if ($this->enabled_modules === null) {
            return true;
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
}
