<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'subscription_status',
        'subscription_expires_at',
        'trial_started_at',
        'trial_expires_at',
        'is_trial',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'subscription_expires_at' => 'date',
        'trial_started_at' => 'datetime',
        'trial_expires_at' => 'datetime',
        'is_trial' => 'boolean',
    ];

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
     * Check if clinic is active and subscription is valid.
     */
    public function isActiveWithValidSubscription(): bool
    {
        return $this->is_active &&
               $this->activated_at !== null &&
               ($this->subscription_expires_at === null || $this->subscription_expires_at->isFuture()) &&
               ($this->is_trial ? $this->isTrialValid() : true);
    }

    /**
     * Start a 7-day free trial.
     */
    public function startTrial(): void
    {
        $this->update([
            'is_trial' => true,
            'trial_started_at' => now(),
            'trial_expires_at' => now()->addDays(7),
            'subscription_status' => 'trial',
            'max_users' => 5, // Trial limit matches Basic plan
        ]);
    }

    /**
     * Check if trial is still valid.
     */
    public function isTrialValid(): bool
    {
        return $this->is_trial &&
               $this->trial_expires_at &&
               $this->trial_expires_at->isFuture();
    }

    /**
     * Check if trial has expired.
     */
    public function isTrialExpired(): bool
    {
        return $this->is_trial &&
               $this->trial_expires_at &&
               $this->trial_expires_at->isPast();
    }

    /**
     * Get remaining trial days.
     */
    public function getRemainingTrialDays(): int
    {
        if (!$this->is_trial || !$this->trial_expires_at) {
            return 0;
        }

        return max(0, now()->diffInDays($this->trial_expires_at, false));
    }

    /**
     * Get trial status message.
     */
    public function getTrialStatusMessage(): string
    {
        if (!$this->is_trial) {
            return 'Not on trial';
        }

        $remainingDays = $this->getRemainingTrialDays();

        if ($remainingDays > 0) {
            return "Trial expires in {$remainingDays} day" . ($remainingDays > 1 ? 's' : '');
        }

        return 'Trial expired';
    }

    /**
     * Convert trial to paid subscription.
     */
    public function convertTrialToSubscription(int $months = 12, string $plan = 'professional', int $maxUsers = 15): void
    {
        $this->update([
            'is_trial' => false,
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addMonths($months),
            'trial_started_at' => null,
            'trial_expires_at' => null,
            'max_users' => $maxUsers,
        ]);

        // Store the plan type in settings
        $this->setSetting('subscription_plan', $plan);
    }

    /**
     * Check if clinic has reached user limit.
     */
    public function hasReachedUserLimit(): bool
    {
        return $this->users()->active()->count() >= $this->max_users;
    }

    /**
     * Get current subscription plan.
     */
    public function getSubscriptionPlan(): string
    {
        return $this->getSetting('subscription_plan', 'trial');
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
            'plan' => $this->getSubscriptionPlan(),
        ];
    }

    /**
     * Get plan details by plan name.
     */
    public static function getPlanDetails(string $planName): array
    {
        $plans = [
            'basic' => [
                'name' => 'Basic Plan',
                'max_users' => 5,
                'price' => '$29',
                'period' => 'month',
            ],
            'professional' => [
                'name' => 'Professional Plan',
                'max_users' => 15,
                'price' => '$59',
                'period' => 'month',
            ],
            'enterprise' => [
                'name' => 'Enterprise Plan',
                'max_users' => 30,
                'price' => '$99',
                'period' => 'month',
            ],
            'trial' => [
                'name' => 'Trial',
                'max_users' => 5,
                'price' => 'Free',
                'period' => '7 days',
            ],
        ];

        return $plans[$planName] ?? $plans['trial'];
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

    /**
     * Scope to filter clinics with valid subscriptions.
     */
    public function scopeWithValidSubscription($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('subscription_expires_at')
              ->orWhere('subscription_expires_at', '>', now());
        });
    }
}
