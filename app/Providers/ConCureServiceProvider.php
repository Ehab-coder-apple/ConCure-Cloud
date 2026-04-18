<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class ConCureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only register view composers if views are available and app is booted
        if (app()->bound('view') && !app()->runningInConsole()) {
            try {
                // Share global data with all views
                View::composer('*', function ($view) {
                    $view->with([
                        'appName' => config('app.name'),
                        'companyName' => config('concure.company_name', 'ConCure'),
                        'primaryColor' => config('concure.primary_color', '#008080'),
                        'supportedLanguages' => config('concure.supported_languages', ['en' => 'English', 'ar' => 'العربية', 'ku' => 'کوردی']),
                    ]);
                });
            } catch (\Exception $e) {
                // Silently fail during bootstrap to prevent breaking the application
                \Log::warning('ConCureServiceProvider: Could not register view composers', ['error' => $e->getMessage()]);
            }
        }

        // Define authorization gates
        $this->defineGates();

        // Set locale based on session or user preference
        $this->setLocale();
    }

    /**
     * Define authorization gates.
     */
    private function defineGates(): void
    {
        // User management gates
        Gate::define('manage-users', function (User $user) {
            return $user->canManageUsers();
        });

        Gate::define('manage-patients', function (User $user) {
            return $user->canManagePatients();
        });



        Gate::define('manage-finance', function (User $user) {
            // Admins are auto-allowed; everyone else needs any broad finance_* permission
            // OR one of the granular *_create perms used by restricted roles (Reception,
            // Lab Tech) that can record transactions but cannot see aggregate data.
            return ($user->isSuperAdmin() || $user->isClinicAdmin()) ||
                $user->hasAnyPermission([
                    'finance_view', 'finance_create', 'finance_edit', 'finance_delete', 'finance_reports', 'finance_approve',
                    'finance_invoices_create', 'finance_expenses_create', 'finance_receipts_create',
                ]);
        });

        Gate::define('manage-advertisements', function (User $user) {
            return $user->hasPermission('settings_system');
        });

        Gate::define('view-audit-logs', function (User $user) {
            return $user->hasAnyPermission(['reports_audit','settings_system']);
        });

        Gate::define('manage-activation-codes', function (User $user) {
            return $user->hasAnyPermission(['users_permissions','settings_system']);
        });

        Gate::define('manage-food-composition', function (User $user) {
            return $user->hasAnyPermission(['food_database_manage','food_database_edit','food_database_create']);
        });

        // System-wide permission gates
        Gate::define('access-section', function (User $user, string $section) {
            return $user->canAccessSection($section);
        });

        // Convenience gate for Settings section without needing a route parameter
        // Allow any admin (with or without clinic) and super admin
        Gate::define('access-settings', function (User $user) {
            return $user->isSuperAdmin() || $user->role === 'admin';
        });


        Gate::define('view-dashboard', function (User $user) {
            return $user->hasPermission('dashboard_view');
        });

        Gate::define('view-patients', function (User $user) {
            return $user->hasPermission('patients_view');
        });

        Gate::define('create-patients', function (User $user) {
            return $user->hasPermission('patients_create');
        });

        Gate::define('edit-patients', function (User $user) {
            return $user->hasPermission('patients_edit');
        });

        Gate::define('delete-patients', function (User $user) {
            return $user->hasPermission('patients_delete');
        });

        Gate::define('view-prescriptions', function (User $user) {
            return $user->hasPermission('prescriptions_view');
        });

        Gate::define('create-prescriptions', function (User $user) {
            return $user->hasPermission('prescriptions_create');
        });

        Gate::define('edit-prescriptions', function (User $user) {
            return $user->hasPermission('prescriptions_edit');
        });

        Gate::define('delete-prescriptions', function (User $user) {
            return $user->hasPermission('prescriptions_delete');
        });

        // Lab request gates
        Gate::define('view-lab-requests', function (User $user) {
            return $user->hasAnyPermission(['lab_view', 'lab_manage', 'prescriptions_view']);
        });

        Gate::define('create-lab-requests', function (User $user) {
            return $user->hasAnyPermission(['lab_create', 'lab_manage', 'prescriptions_create']);
        });

        Gate::define('edit-lab-requests', function (User $user) {
            return $user->hasAnyPermission(['lab_edit', 'lab_manage', 'prescriptions_edit']);
        });

        Gate::define('delete-lab-requests', function (User $user) {
            return $user->hasAnyPermission(['lab_delete', 'lab_manage', 'prescriptions_delete']);
        });

        // Radiology request gates
        Gate::define('view-radiology-requests', function (User $user) {
            return $user->hasAnyPermission(['radiology_view', 'radiology_manage', 'prescriptions_view']);
        });

        Gate::define('create-radiology-requests', function (User $user) {
            return $user->hasAnyPermission(['radiology_create', 'radiology_manage', 'prescriptions_create']);
        });

        Gate::define('edit-radiology-requests', function (User $user) {
            return $user->hasAnyPermission(['radiology_edit', 'radiology_manage', 'prescriptions_edit']);
        });

        Gate::define('delete-radiology-requests', function (User $user) {
            return $user->hasAnyPermission(['radiology_delete', 'radiology_manage', 'prescriptions_delete']);
        });

        Gate::define('view-appointments', function (User $user) {
            return $user->hasPermission('appointments_view');
        });

        Gate::define('create-appointments', function (User $user) {
            return $user->hasPermission('appointments_create');
        });

        Gate::define('edit-appointments', function (User $user) {
            return $user->hasPermission('appointments_edit');
        });

        Gate::define('delete-appointments', function (User $user) {
            return $user->hasPermission('appointments_delete');
        });

        Gate::define('view-medicines', function (User $user) {
            return $user->hasPermission('medicines_view');
        });

        Gate::define('create-medicines', function (User $user) {
            return $user->hasPermission('medicines_create');
        });

        Gate::define('edit-medicines', function (User $user) {
            return $user->hasPermission('medicines_edit');
        });

        Gate::define('delete-medicines', function (User $user) {
            return $user->hasPermission('medicines_delete');
        });

        Gate::define('view-nutrition-plans', function (User $user) {
            return $user->hasPermission('nutrition_view');
        });

        Gate::define('create-nutrition-plans', function (User $user) {
            return $user->hasPermission('nutrition_create');
        });

        Gate::define('edit-nutrition-plans', function (User $user) {
            return $user->hasPermission('nutrition_edit');
        });

        Gate::define('delete-nutrition-plans', function (User $user) {
            return $user->hasPermission('nutrition_delete');
        });

        Gate::define('view-finance', function (User $user) {
            return $user->hasPermission('finance_view');
        });

        Gate::define('create-finance', function (User $user) {
            return $user->hasPermission('finance_create');
        });

        Gate::define('edit-finance', function (User $user) {
            return $user->hasPermission('finance_edit');
        });

        Gate::define('delete-finance', function (User $user) {
            return $user->hasPermission('finance_delete');
        });

        Gate::define('finance-reports', function (User $user) {
            return $user->hasPermission('finance_reports');
        });

        // Read access to the finance dashboard / listings (excludes create-only users)
        Gate::define('view-finance-dashboard', function (User $user) {
            return $user->canViewFinance();
        });

        // Granular "create only" gates for restricted roles (e.g. Reception, Lab Tech)
        Gate::define('finance-invoices-create', function (User $user) {
            return $user->canCreateInvoices();
        });

        Gate::define('finance-expenses-create', function (User $user) {
            return $user->canCreateExpenses();
        });

        Gate::define('finance-receipts-create', function (User $user) {
            return $user->canCreateReceipts();
        });


        Gate::define('view-users', function (User $user) {
            return $user->hasPermission('users_view');
        });

        Gate::define('create-users', function (User $user) {
            return $user->hasPermission('users_create');
        });

        Gate::define('edit-users', function (User $user) {
            return $user->hasPermission('users_edit');
        });

        Gate::define('delete-users', function (User $user) {
            return $user->hasPermission('users_delete');
        });

        Gate::define('view-settings', function (User $user) {
            return $user->hasPermission('settings_view');
        });

        Gate::define('edit-settings', function (User $user) {
            return $user->hasPermission('settings_edit');
        });

        Gate::define('view-reports', function (User $user) {
            return $user->hasPermission('reports_view');
        });

        Gate::define('generate-reports', function (User $user) {
            return $user->hasPermission('reports_generate');
        });

        // Clinic-specific gates
        Gate::define('manage-clinic-settings', function (User $user) {
            return $user->hasPermission('settings_clinic');
        });

        Gate::define('view-clinic-reports', function (User $user) {
            return $user->hasPermission('reports_view');
        });

        // Patient-specific gates
        Gate::define('view-patient', function (User $user, $patient) {
            if ($user->role === 'patient') {
                return $user->id === $patient->user_id; // If patient has user account
            }
            return $user->hasPermission('patients_view') || $user->canManagePatients();
        });

        Gate::define('edit-patient', function (User $user, $patient) {
            return $user->hasPermission('patients_edit') || $user->canManagePatients();
        });

        Gate::define('delete-patient', function (User $user, $patient) {
            return $user->hasPermission('patients_delete') || $user->canManagePatients();
        });

        // Financial gates
        Gate::define('approve-discounts', function (User $user) {
            return $user->hasPermission('finance_approve');
        });

        Gate::define('approve-expenses', function (User $user) {
            return $user->hasPermission('finance_approve');
        });

        // Communication gates
        Gate::define('send-communications', function (User $user) {
            return $user->canAccessSection('patients') || $user->canAccessSection('appointments') || $user->hasAnyPermission(['prescriptions_view','prescriptions_create']);
        });
    }

    /**
     * Set application locale.
     */
    private function setLocale(): void
    {
        try {
            $supportedLanguages = array_keys(config('concure.supported_languages', ['en' => 'English', 'ar' => 'العربية', 'ku' => 'کوردی']));

            // Check session for locale
            if (session()->has('locale')) {
                $locale = session('locale');
                if (in_array($locale, $supportedLanguages)) {
                    app()->setLocale($locale);
                    return;
                }
            }

            // Check authenticated user's language preference
            if (auth()->check() && auth()->user() && auth()->user()->language) {
                $locale = auth()->user()->language;
                if (in_array($locale, $supportedLanguages)) {
                    app()->setLocale($locale);
                    session(['locale' => $locale]);
                    return;
                }
            }

            // Fall back to default locale
            app()->setLocale(config('concure.default_language', 'en'));
        } catch (\Exception $e) {
            // Fallback to English if anything goes wrong
            app()->setLocale('en');
        }
    }
}
