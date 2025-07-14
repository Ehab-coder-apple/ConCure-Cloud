<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterDashboardController;
use App\Http\Controllers\MasterAuthController;

/*
|--------------------------------------------------------------------------
| Master Dashboard Routes
|--------------------------------------------------------------------------
|
| These routes are for the SaaS platform master dashboard only.
| They are completely separate from tenant clinic routes.
| Only Program Owners can access these routes.
|
*/

// Master Authentication Routes
Route::prefix('master')->name('master.')->group(function () {

    // Master Login Routes (no middleware for login pages)
    Route::get('/login', [MasterAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [MasterAuthController::class, 'login'])->name('login.submit');

    // Master Dashboard Routes (custom master auth middleware)
    Route::middleware(['master.auth'])->group(function () {
        
        // Main Dashboard
        Route::get('/dashboard', [MasterDashboardController::class, 'index'])->name('dashboard');
        
        // Clinic Management
        Route::get('/clinics', [MasterDashboardController::class, 'clinics'])->name('clinics');
        Route::get('/clinics/{clinic}', [MasterDashboardController::class, 'showClinic'])->name('clinics.show');
        Route::patch('/clinics/{clinic}/toggle-status', [MasterDashboardController::class, 'toggleClinicStatus'])->name('clinics.toggle-status');
        Route::patch('/clinics/{clinic}/extend-subscription', [MasterDashboardController::class, 'extendSubscription'])->name('clinics.extend-subscription');
        Route::patch('/clinics/{clinic}/extend-trial', [MasterDashboardController::class, 'extendTrial'])->name('clinics.extend-trial');
        Route::patch('/clinics/{clinic}/convert-trial', [MasterDashboardController::class, 'convertTrialToSubscription'])->name('clinics.convert-trial');
        Route::delete('/clinics/{clinic}', [MasterDashboardController::class, 'deleteClinic'])->name('clinics.delete');

        // Route aliases for backward compatibility
        Route::patch('/clinic/{clinic}/toggle-status', [MasterDashboardController::class, 'toggleClinicStatus'])->name('clinic.toggle-status');
        Route::patch('/clinic/{clinic}/extend-subscription', [MasterDashboardController::class, 'extendSubscription'])->name('clinic.extend-subscription');
        
        // Activation Code Management
        Route::get('/activation-codes', [MasterDashboardController::class, 'activationCodes'])->name('activation-codes');
        Route::post('/activation-codes/generate', [MasterDashboardController::class, 'generateActivationCode'])->name('activation-codes.generate');
        Route::post('/generate-activation-code', [MasterDashboardController::class, 'generateActivationCode'])->name('generate-code');
        Route::delete('/activation-codes/{code}', [MasterDashboardController::class, 'deleteActivationCode'])->name('activation-codes.delete');
        Route::patch('/activation-codes/{code}/extend', [MasterDashboardController::class, 'extendActivationCode'])->name('activation-codes.extend');
        
        // Registration Requests Management
        Route::get('/registration-requests', [MasterDashboardController::class, 'registrationRequests'])->name('registration-requests');
        Route::patch('/registration-requests/{request}/approve', [MasterDashboardController::class, 'approveRegistration'])->name('registration-requests.approve');
        Route::patch('/registration-requests/{request}/reject', [MasterDashboardController::class, 'rejectRegistration'])->name('registration-requests.reject');
        
        // Analytics and Reports
        Route::get('/analytics', [MasterDashboardController::class, 'analytics'])->name('analytics');
        Route::get('/analytics/export', [MasterDashboardController::class, 'exportAnalytics'])->name('analytics.export');
        Route::get('/analytics/trials', [MasterDashboardController::class, 'trialAnalytics'])->name('analytics.trials');
        Route::get('/reports/usage', [MasterDashboardController::class, 'usageReport'])->name('reports.usage');
        Route::get('/reports/revenue', [MasterDashboardController::class, 'revenueReport'])->name('reports.revenue');

        // Program Features
        Route::get('/program-features', [MasterDashboardController::class, 'programFeatures'])->name('program-features');
        
        // System Settings
        Route::get('/settings', [MasterDashboardController::class, 'settings'])->name('settings');
        Route::patch('/settings', [MasterDashboardController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/update-software', [MasterDashboardController::class, 'updateSoftware'])->name('settings.update-software');
        
        // Platform Users (Master Admins)
        Route::get('/platform-users', [MasterDashboardController::class, 'platformUsers'])->name('platform-users');
        Route::post('/platform-users', [MasterDashboardController::class, 'createPlatformUser'])->name('platform-users.create');
        Route::patch('/platform-users/{user}', [MasterDashboardController::class, 'updatePlatformUser'])->name('platform-users.update');
        Route::delete('/platform-users/{user}', [MasterDashboardController::class, 'deletePlatformUser'])->name('platform-users.delete');
        
        // Audit Logs
        Route::get('/audit-logs', [MasterDashboardController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/audit-logs/export', [MasterDashboardController::class, 'exportAuditLogs'])->name('audit-logs.export');
        
        // System Health
        Route::get('/system-health', [MasterDashboardController::class, 'systemHealth'])->name('system-health');
        Route::get('/system-health/check', [MasterDashboardController::class, 'runHealthCheck'])->name('system-health.check');
        
        // Backup Management
        Route::get('/backups', [MasterDashboardController::class, 'backups'])->name('backups');
        Route::post('/backups/create', [MasterDashboardController::class, 'createBackup'])->name('backups.create');
        Route::delete('/backups/{backup}', [MasterDashboardController::class, 'deleteBackup'])->name('backups.delete');
        Route::get('/backups/{backup}/download', [MasterDashboardController::class, 'downloadBackup'])->name('backups.download');
        
        // Logout
        Route::post('/logout', [MasterAuthController::class, 'logout'])->name('logout');
    });
});

// Master Dashboard Root (redirect to login if not authenticated)
// Note: This route now redirects to the new master control welcome page for unauthenticated users
Route::get('/master', function () {
    if (auth()->check() && in_array(auth()->user()->role, ['program_owner', 'platform_admin', 'support_agent'])) {
        return redirect()->route('master.dashboard');
    }
    return redirect()->route('master.welcome.index');
});
