<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\AuthController;
use App\Http\Controllers\Master\DashboardController;
use App\Http\Controllers\Master\ClinicController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\SubscriptionController;
use App\Http\Controllers\Master\ReportController;

/*
|--------------------------------------------------------------------------
| Master Routes
|--------------------------------------------------------------------------
|
| These routes are for the master/super admin interface to manage
| all clinics, users, and system-wide settings.
|
*/

// Master Authentication Routes (Guest only)
Route::middleware(['super.guest'])->group(function () {
    Route::get('/master/login', [AuthController::class, 'showLoginForm'])->name('master.login');
    Route::post('/master/login', [AuthController::class, 'login']);
    Route::get('/master/register', [AuthController::class, 'showRegistrationForm'])->name('master.register');
    Route::post('/master/register', [AuthController::class, 'register']);
});

// Master Authenticated Routes
Route::middleware(['super.admin'])->prefix('master')->name('master.')->group(function () {
    
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/clinic-status', [DashboardController::class, 'getClinicStatusData'])->name('dashboard.clinic-status');
    Route::get('/dashboard/user-roles', [DashboardController::class, 'getUserRoleData'])->name('dashboard.user-roles');
    Route::get('/dashboard/system-health', [DashboardController::class, 'getSystemHealth'])->name('dashboard.system-health');
    Route::get('/dashboard/pending-registrations', [DashboardController::class, 'getPendingRegistrations'])->name('dashboard.pending-registrations');
    Route::post('/dashboard/approve-clinic/{clinic}', [DashboardController::class, 'approveClinic'])->name('dashboard.approve-clinic');
    Route::post('/dashboard/reject-clinic/{clinic}', [DashboardController::class, 'rejectClinic'])->name('dashboard.reject-clinic');
    
    // Clinic Management
    Route::resource('clinics', ClinicController::class);
    Route::patch('/clinics/{clinic}/activate', [ClinicController::class, 'activate'])->name('clinics.activate');
    Route::patch('/clinics/{clinic}/deactivate', [ClinicController::class, 'deactivate'])->name('clinics.deactivate');
    Route::post('/clinics/{clinic}/reset-admin-password', [ClinicController::class, 'resetAdminPassword'])->name('clinics.reset-admin-password');
    
    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Subscription Management
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/create', [SubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::get('/subscriptions/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('subscriptions.edit');
    Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    
    // System Settings
    Route::get('/settings', function () {
        return view('master.settings.index');
    })->name('settings');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
});

// Redirect /master to dashboard if authenticated, login if not
Route::get('/master', function () {
    if (auth()->check() && auth()->user()->isSuperAdmin()) {
        return redirect()->route('master.dashboard');
    }
    return redirect()->route('master.login');
})->name('master.home');
