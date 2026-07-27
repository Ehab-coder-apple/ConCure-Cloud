<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\AuthController;
use App\Http\Controllers\Master\DashboardController;
use App\Http\Controllers\Master\ClinicController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\SubscriptionController;
use App\Http\Controllers\Master\ReportController;
use App\Http\Controllers\Master\ClinicReportController;
use App\Http\Controllers\Master\PaymentsController;
use App\Http\Controllers\Master\PlanController;
use App\Http\Controllers\Master\MaintenanceController;
use App\Http\Controllers\Master\SettingsController;
use App\Http\Controllers\Master\FinanceController;
use App\Http\Controllers\StorageQuotaController;

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
    Route::get('/logout', function () {
        return redirect()->route('master.login')->with('info', 'Please use the logout button in the sidebar.');
    });

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
    Route::post('/clinics/{clinic}/whatsapp-config', [ClinicController::class, 'configureWhatsApp'])->name('clinics.whatsapp-config');
    Route::post('/clinics/{clinic}/reset-demo', [ClinicController::class, 'resetDemoClinic'])->name('clinics.reset-demo');

    // Contract Management for Existing Clinics
    Route::get('/clinics/{clinic}/contracts', [ClinicController::class, 'manageContract'])->name('clinics.manage-contract');
    Route::post('/clinics/{clinic}/contracts', [ClinicController::class, 'storeContract'])->name('clinics.store-contract');
    Route::post('/clinics/{clinic}/contracts/{contract}/renew', [ClinicController::class, 'renewContract'])->name('clinics.renew-contract');
    Route::post('/clinics/{clinic}/contracts/{contract}/send', [ClinicController::class, 'sendContract'])->name('clinics.send-contract');
    Route::delete('/clinics/{clinic}/contracts/{contract}', [ClinicController::class, 'deleteContract'])->name('clinics.delete-contract');
    Route::get('/clinics/{clinic}/contracts/{contract}/pdf', [ClinicController::class, 'downloadContractPdf'])->name('clinics.contract-pdf');

    // Storage Quota Management
    Route::post('/clinics/{clinic}/update-storage-limit', [StorageQuotaController::class, 'updateStorageLimit'])->name('clinics.update-storage-limit');
    Route::post('/clinics/{clinic}/sync-storage', [StorageQuotaController::class, 'syncStorage'])->name('clinics.sync-storage');
    Route::get('/clinics/{clinic}/storage-info', [StorageQuotaController::class, 'getClinicStorageInfo'])->name('clinics.storage-info');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Plans CRUD
    Route::resource('plans', PlanController::class)->except(['show']);

    // Subscription Management (per-clinic assignment)
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/create', [SubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::get('/subscriptions/{subscription}/edit', [SubscriptionController::class, 'edit'])->name('subscriptions.edit');
    Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    // Maintenance (super admin only)
    Route::post('/maintenance/server-update', [MaintenanceController::class, 'runServerUpdate'])->name('maintenance.server-update');

    // System Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/timezone', [SettingsController::class, 'updateTimezone'])->name('settings.update-timezone');
    Route::post('/settings/branding-logo', [SettingsController::class, 'updateBrandingLogo'])->name('settings.update-branding-logo');
    Route::delete('/settings/branding-logo', [SettingsController::class, 'deleteBrandingLogo'])->name('settings.delete-branding-logo');
    Route::post('/settings/import-sql', [SettingsController::class, 'importSql'])->name('settings.import-sql');
    Route::post('/settings/contract-template', [SettingsController::class, 'updateContractTemplate'])->name('settings.update-contract-template');
    Route::post('/settings/contract-template/reset', [SettingsController::class, 'resetContractTemplate'])->name('settings.reset-contract-template');

    // Features Documentation
    Route::get('/features', [DashboardController::class, 'features'])->name('features');
    Route::get('/features/pdf', [DashboardController::class, 'featuresPdf'])->name('features.pdf');

    // Finance
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/finance/invoice/store', [FinanceController::class, 'storeInvoice'])->name('finance.invoice.store');
    Route::put('/finance/invoice/{invoice}/update', [FinanceController::class, 'updateInvoice'])->name('finance.invoice.update');
    Route::delete('/finance/invoice/{invoice}/delete', [FinanceController::class, 'deleteInvoice'])->name('finance.invoice.delete');
    Route::post('/finance/invoice/{invoice}/record-payment', [FinanceController::class, 'recordInvoicePayment'])->name('finance.invoice.record-payment');
    Route::post('/finance/payment/store', [FinanceController::class, 'recordPayment'])->name('finance.payment.store');
    Route::put('/finance/payment/{payment}/update', [FinanceController::class, 'updatePayment'])->name('finance.payment.update');
    Route::delete('/finance/payment/{payment}/delete', [FinanceController::class, 'deletePayment'])->name('finance.payment.delete');
    Route::get('/finance/invoices', [FinanceController::class, 'invoices'])->name('finance.invoices');
    Route::get('/finance/payments', [FinanceController::class, 'payments'])->name('finance.payments');
    Route::get('/finance/invoice/{invoice}', [FinanceController::class, 'showInvoice'])->name('finance.invoice.show');
    Route::get('/finance/invoice/{invoice}/print', [FinanceController::class, 'printInvoice'])->name('finance.invoice.print');
    Route::get('/finance/invoice/{invoice}/pdf', [FinanceController::class, 'downloadInvoicePDF'])->name('finance.invoice.pdf');

    // Master expenses (platform operating costs - IQD only, super-admin only)
    Route::get('/finance/expenses', [FinanceController::class, 'expenses'])->name('finance.expenses');
    Route::post('/finance/expense/store', [FinanceController::class, 'storeExpense'])->name('finance.expense.store');
    Route::put('/finance/expense/{expense}/update', [FinanceController::class, 'updateExpense'])->name('finance.expense.update');
    Route::delete('/finance/expense/{expense}/delete', [FinanceController::class, 'deleteExpense'])->name('finance.expense.delete');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/clinics', [ClinicReportController::class, 'index'])->name('reports.clinics');
    Route::post('/reports/payments', [PaymentsController::class, 'store'])->name('reports.payments.store');
    Route::post('/reports/payments/import', [PaymentsController::class, 'import'])->name('reports.payments.import');
    Route::get('/reports/service-charges/export', [ReportController::class, 'exportServiceCharges'])->name('reports.service-charges.export');

    // Login/Logout Activity Report
    Route::get('/reports/login-activity', [ReportController::class, 'loginActivity'])->name('reports.login-activity');
    Route::get('/reports/login-activity/export', [ReportController::class, 'exportLoginActivity'])->name('reports.login-activity.export');

});

// Redirect /master to dashboard if authenticated, login if not
Route::get('/master', function () {
    if (auth()->check() && auth()->user()->canAccessMasterDashboard()) {
        return redirect()->route('master.dashboard');
    }
    return redirect()->route('master.login');
})->name('master.home');
