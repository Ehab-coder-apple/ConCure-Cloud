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
        'logo',
        'settings',
        'is_active',
        'max_users',
        'activated_at',
        'activation_code',
        'plan_id',
        'billing_cycle',
        'next_billing_at',
        'custom_monthly_price',
        'custom_yearly_price',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'custom_monthly_price' => 'decimal:2',
        'custom_yearly_price' => 'decimal:2',
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
