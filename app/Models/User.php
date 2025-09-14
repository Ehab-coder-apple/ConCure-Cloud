<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'role',
        'title_prefix',
        'is_active',
        'activation_code',
        'activated_at',
        'expires_at',
        'language',
        'permissions',
        'metadata',
        'clinic_id',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * User roles
     */
    const ROLES = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'doctor' => 'Doctor',
        'nutritionist' => 'Nutritionist',
        'assistant' => 'Assistant',
        'nurse' => 'Nurse',
        'accountant' => 'Accountant',
        'patient' => 'Patient',
    ];

    /**
     * Get the clinic that owns the user.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias for creator relation to match existing views expecting createdBy.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    /**
     * Get the users created by this user.
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Get the patients created by this user.
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'created_by');
    }

    /**
     * Get the appointments for this doctor.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * Get the appointments created by this user.
     */
    public function createdAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'created_by');
    }

    /**
     * Get the audit logs for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the activation codes created by this user.
     */
    public function createdActivationCodes(): HasMany
    {
        return $this->hasMany(ActivationCode::class, 'created_by');
    }

    /**
     * Get the activation codes used by this user.
     */
    public function usedActivationCodes(): HasMany
    {
        return $this->hasMany(ActivationCode::class, 'used_by');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the specified roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        // DEVELOPMENT MODE: Disable all role checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return true;
        }

        return in_array($this->role, $roles);
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is a clinic admin.
     */
    public function isClinicAdmin(): bool
    {
        return $this->role === 'admin' && $this->clinic_id !== null;
    }

    /**
     * Check if user can access master dashboard.
     */
    public function canAccessMasterDashboard(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can manage all clinics.
     */
    public function canManageAllClinics(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user is active and activated.
     */
    public function isActiveAndActivated(): bool
    {
        return $this->is_active && $this->activated_at !== null;
    }

    /**
     * Get user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get user's full name with title prefix.
     */
    public function getFullNameWithTitleAttribute(): string
    {
        $prefix = $this->title_prefix ?: $this->getDefaultTitlePrefix();
        return $prefix ? $prefix . ' ' . $this->full_name : $this->full_name;
    }

    /**
     * Get default title prefix based on role.
     */
    public function getDefaultTitlePrefix(): ?string
    {
        $defaultPrefixes = [
            'doctor' => 'Dr.',
            'nutritionist' => 'Nutritionist',
            'nurse' => 'Nurse',
            'admin' => null,
            'assistant' => null,
            'accountant' => null,
            'patient' => null,
        ];

        return $defaultPrefixes[$this->role] ?? null;
    }

    /**
     * Get available title prefixes for the user's role.
     */
    public function getAvailableTitlePrefixes(): array
    {
        $prefixes = [
            'doctor' => ['Dr.', 'Prof.', 'Prof. Dr.', 'Assoc. Prof.', 'Asst. Prof.'],
            'nutritionist' => ['Nutritionist', 'Clinical Nutritionist', 'Registered Dietitian', 'RD', 'RDN'],
            'nurse' => ['Nurse', 'RN', 'LPN', 'Nurse Practitioner', 'NP'],
            'admin' => ['Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Prof.'],
            'assistant' => ['Mr.', 'Ms.', 'Mrs.'],
            'accountant' => ['Mr.', 'Ms.', 'Mrs.', 'CPA'],
            'patient' => ['Mr.', 'Ms.', 'Mrs.'],
        ];

        return $prefixes[$this->role] ?? ['Mr.', 'Ms.', 'Mrs.'];
    }

    /**
     * Get user's role display name.
     */
    public function getRoleDisplayAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }





    /**
     * Scope to filter by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to filter active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter activated users.
     */
    public function scopeActivated($query)
    {
        return $query->whereNotNull('activated_at');
    }

    /**
     * Check if user can manage patients.
     */
    public function canManagePatients(): bool
    {
        // DEVELOPMENT MODE: Disable all role checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return true;
        }

        return in_array($this->role, ['admin', 'doctor', 'nutritionist', 'assistant', 'nurse']);
    }

    /**
     * Check if user can prescribe.
     */
    public function canPrescribe(): bool
    {
        return $this->role === 'doctor';
    }

    /**
     * Check if user can access finance.
     */
    public function canAccessFinance(): bool
    {
        return in_array($this->role, ['admin', 'accountant']);
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user can view nutrition plans.
     */
    public function canViewNutritionPlans(): bool
    {
        return $this->hasAnyPermission(['nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete', 'nutrition_manage']);
    }

    /**
     * Check if user can create nutrition plans.
     */
    public function canCreateNutritionPlans(): bool
    {
        // DEVELOPMENT MODE: Disable all permission checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return true;
        }

        return $this->hasAnyPermission(['nutrition_create', 'nutrition_manage']);
    }

    /**
     * Check if user can edit nutrition plans.
     */
    public function canEditNutritionPlans(): bool
    {
        return $this->hasAnyPermission(['nutrition_edit', 'nutrition_manage']);
    }

    /**
     * Check if user can delete nutrition plans.
     */
    public function canDeleteNutritionPlans(): bool
    {
        return $this->hasAnyPermission(['nutrition_delete', 'nutrition_manage']);
    }

    /**
     * Check if user can view radiology requests.
     */
    public function canViewRadiologyRequests(): bool
    {
        return $this->hasAnyPermission(['radiology_view', 'radiology_create', 'radiology_edit', 'radiology_delete', 'radiology_manage']);
    }

    /**
     * Check if user can create radiology requests.
     */
    public function canCreateRadiologyRequests(): bool
    {
        // DEVELOPMENT MODE: Disable all permission checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return true;
        }

        return $this->hasAnyPermission(['radiology_create', 'radiology_manage']);
    }

    /**
     * Check if user can edit radiology requests.
     */
    public function canEditRadiologyRequests(): bool
    {
        return $this->hasAnyPermission(['radiology_edit', 'radiology_manage']);
    }

    /**
     * Check if user can delete radiology requests.
     */
    public function canDeleteRadiologyRequests(): bool
    {
        return $this->hasAnyPermission(['radiology_delete', 'radiology_manage']);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // DEVELOPMENT MODE: Disable all permission checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Check if user has any of the specified permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        // DEVELOPMENT MODE: Disable all permission checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return true;
        }

        $userPermissions = $this->permissions ?? [];
        return !empty(array_intersect($permissions, $userPermissions));
    }

    /**
     * Check if user has all of the specified permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        // DEVELOPMENT MODE: Disable all permission checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return true;
        }

        $userPermissions = $this->permissions ?? [];
        return empty(array_diff($permissions, $userPermissions));
    }

    /**
     * Add a permission to the user.
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
        }
    }

    /**
     * Remove a permission from the user.
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_filter($permissions, fn($p) => $p !== $permission);
        $this->permissions = array_values($permissions);
    }

    /**
     * Set multiple permissions at once.
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = array_values(array_unique($permissions));
    }

    /**
     * Get user's permissions for a specific section.
     */
    public function getPermissionsForSection(string $section): array
    {
        $allPermissions = self::getAllPermissions();
        $sectionPermissions = $allPermissions[$section] ?? [];
        $userPermissions = $this->permissions ?? [];

        return array_intersect(array_keys($sectionPermissions), $userPermissions);
    }

    /**
     * Check if user can access a specific section.
     */
    public function canAccessSection(string $section): bool
    {
        // DEVELOPMENT MODE: Disable all permission checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return true;
        }

        $allPermissions = self::getAllPermissions();
        $sectionPermissions = array_keys($allPermissions[$section] ?? []);

        return $this->hasAnyPermission($sectionPermissions);
    }

    /**
     * Get all available nutrition permissions.
     */
    public static function getNutritionPermissions(): array
    {
        return [
            'nutrition_view' => 'View Nutrition Plans',
            'nutrition_create' => 'Create Nutrition Plans',
            'nutrition_manage' => 'Full Nutrition Management',
        ];
    }

    /**
     * Get all available system permissions organized by section.
     */
    public static function getAllPermissions(): array
    {
        return [
            'dashboard' => [
                'dashboard_view' => 'View Dashboard',
                'dashboard_stats' => 'View Dashboard Statistics',
            ],
            'patients' => [
                'patients_view' => 'View Patients',
                'patients_create' => 'Create Patients',
                'patients_edit' => 'Edit Patients',
                'patients_delete' => 'Delete Patients',
                'patients_files' => 'Manage Patient Files',
                'patients_history' => 'View Patient History',
            ],
            'prescriptions' => [
                'prescriptions_view' => 'View Prescriptions',
                'prescriptions_create' => 'Create Prescriptions',
                'prescriptions_edit' => 'Edit Prescriptions',
                'prescriptions_delete' => 'Delete Prescriptions',
                'prescriptions_print' => 'Print Prescriptions',
            ],
            'appointments' => [
                'appointments_view' => 'View Appointments',
                'appointments_create' => 'Create Appointments',
                'appointments_edit' => 'Edit Appointments',
                'appointments_delete' => 'Delete Appointments',
                'appointments_manage' => 'Manage All Appointments',
            ],
            'medicines' => [
                'medicines_view' => 'View Medicines',
                'medicines_create' => 'Create Medicines',
                'medicines_edit' => 'Edit Medicines',
                'medicines_delete' => 'Delete Medicines',
                'medicines_inventory' => 'Manage Inventory',
            ],
            'nutrition' => [
                'nutrition_view' => 'View Nutrition Plans',
                'nutrition_create' => 'Create Nutrition Plans',
                'nutrition_edit' => 'Edit Nutrition Plans',
                'nutrition_delete' => 'Delete Nutrition Plans',
                'nutrition_manage' => 'Full Nutrition Management',
            ],
            'radiology' => [
                'radiology_view' => 'View Radiology Requests',
                'radiology_create' => 'Create Radiology Requests',
                'radiology_edit' => 'Edit Radiology Requests',
                'radiology_delete' => 'Delete Radiology Requests',
                'radiology_manage' => 'Full Radiology Management',
            ],
            'food_database' => [
                'food_database_view' => 'View Food Database',
                'food_database_create' => 'Add Food Items',
                'food_database_edit' => 'Edit Food Items',
                'food_database_delete' => 'Delete Food Items',
                'food_database_import' => 'Import Food Lists',
                'food_database_export' => 'Export Food Data',
                'food_database_groups' => 'Manage Food Groups',
                'food_database_clear' => 'Clear All Foods',
                'food_database_manage' => 'Full Food Database Management',
            ],
            'finance' => [
                'finance_view' => 'View Financial Data',
                'finance_create' => 'Create Financial Records',
                'finance_edit' => 'Edit Financial Records',
                'finance_delete' => 'Delete Financial Records',
                'finance_reports' => 'View Financial Reports',
                'finance_approve' => 'Approve Financial Transactions',
            ],
            'users' => [
                'users_view' => 'View Users',
                'users_create' => 'Create Users',
                'users_edit' => 'Edit Users',
                'users_delete' => 'Delete Users',
                'users_permissions' => 'Manage User Permissions',
            ],
            'settings' => [
                'settings_view' => 'View Settings',
                'settings_edit' => 'Edit Settings',
                'settings_clinic' => 'Manage Clinic Settings',
                'settings_system' => 'Manage System Settings',
            ],
            'reports' => [
                'reports_view' => 'View Reports',
                'reports_generate' => 'Generate Reports',
                'reports_export' => 'Export Reports',
                'reports_audit' => 'View Audit Logs',
            ],
        ];
    }

    /**
     * Get permission sections for display.
     */
    public static function getPermissionSections(): array
    {
        return [
            'dashboard' => [
                'name' => 'Dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'color' => 'primary',
            ],
            'patients' => [
                'name' => 'Patient Management',
                'icon' => 'fas fa-users',
                'color' => 'info',
            ],
            'prescriptions' => [
                'name' => 'Prescriptions',
                'icon' => 'fas fa-prescription-bottle-alt',
                'color' => 'warning',
            ],
            'appointments' => [
                'name' => 'Appointments',
                'icon' => 'fas fa-calendar-alt',
                'color' => 'success',
            ],
            'medicines' => [
                'name' => 'Medicine Inventory',
                'icon' => 'fas fa-pills',
                'color' => 'danger',
            ],
            'nutrition' => [
                'name' => 'Nutrition Plans',
                'icon' => 'fas fa-apple-alt',
                'color' => 'success',
            ],
            'food_database' => [
                'name' => 'Food Database',
                'icon' => 'fas fa-database',
                'color' => 'info',
            ],
            'finance' => [
                'name' => 'Financial Management',
                'icon' => 'fas fa-dollar-sign',
                'color' => 'warning',
            ],
            'users' => [
                'name' => 'User Management',
                'icon' => 'fas fa-user-cog',
                'color' => 'secondary',
            ],
            'settings' => [
                'name' => 'System Settings',
                'icon' => 'fas fa-cogs',
                'color' => 'dark',
            ],
            'reports' => [
                'name' => 'Reports & Analytics',
                'icon' => 'fas fa-chart-bar',
                'color' => 'info',
            ],
        ];
    }

    /**
     * Get suggested permissions based on user role.
     */
    public static function getSuggestedPermissions(string $role): array
    {
        $suggestions = [
            'admin' => [
                // Full access to everything
                'dashboard_view', 'dashboard_stats',
                'patients_view', 'patients_create', 'patients_edit', 'patients_delete', 'patients_files', 'patients_history',
                'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_delete', 'prescriptions_print',
                'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete', 'appointments_manage',
                'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete', 'medicines_inventory',
                'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete', 'nutrition_manage',
                'radiology_view', 'radiology_create', 'radiology_edit', 'radiology_delete', 'radiology_manage',
                'food_database_view', 'food_database_create', 'food_database_edit', 'food_database_delete', 'food_database_import', 'food_database_export', 'food_database_groups', 'food_database_clear', 'food_database_manage',
                'finance_view', 'finance_create', 'finance_edit', 'finance_delete', 'finance_reports', 'finance_approve',
                'users_view', 'users_create', 'users_edit', 'users_delete', 'users_permissions',
                'settings_view', 'settings_edit', 'settings_clinic', 'settings_system',
                'reports_view', 'reports_generate', 'reports_export', 'reports_audit',
            ],
            'doctor' => [
                // Medical focus with patient care
                'dashboard_view', 'dashboard_stats',
                'patients_view', 'patients_create', 'patients_edit', 'patients_files', 'patients_history',
                'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_print',
                'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_manage',
                'medicines_view', 'medicines_create',
                'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_manage',
                'radiology_view', 'radiology_create', 'radiology_edit', 'radiology_manage',
                'food_database_view', 'food_database_create', 'food_database_edit', 'food_database_import', 'food_database_groups',
                'reports_view', 'reports_generate',
            ],
            'nutritionist' => [
                // Nutrition and diet focus
                'dashboard_view', 'dashboard_stats',
                'patients_view', 'patients_create', 'patients_edit', 'patients_files', 'patients_history',
                'appointments_view', 'appointments_create', 'appointments_edit',
                'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_manage', 'nutrition_delete',
                'radiology_view', 'radiology_create', 'radiology_edit', 'radiology_manage', 'radiology_delete',
                'food_database_view', 'food_database_create', 'food_database_edit', 'food_database_import', 'food_database_groups', 'food_database_delete',
                'reports_view', 'reports_generate',
            ],
            'assistant' => [
                // Administrative support
                'dashboard_view',
                'patients_view', 'patients_create', 'patients_edit', 'patients_files',
                'appointments_view', 'appointments_create', 'appointments_edit',
                'prescriptions_view',
                'medicines_view',
                'nutrition_view',
                'food_database_view', 'food_database_create',
            ],
            'nurse' => [
                // Patient care focus
                'dashboard_view',
                'patients_view', 'patients_edit', 'patients_files', 'patients_history',
                'appointments_view', 'appointments_edit',
                'prescriptions_view',
                'medicines_view',
                'nutrition_view', 'nutrition_create',
                'food_database_view', 'food_database_create',
            ],
            'accountant' => [
                // Financial focus
                'dashboard_view', 'dashboard_stats',
                'patients_view',
                'finance_view', 'finance_create', 'finance_edit', 'finance_reports',
                'reports_view', 'reports_generate', 'reports_export',
            ],
            'patient' => [
                // Very limited access
                'dashboard_view',
                'appointments_view',
            ],
        ];

        return $suggestions[$role] ?? [];
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
}
