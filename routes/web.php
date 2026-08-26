<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\SimplePrescriptionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClinicActivationController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\FoodGroupController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\MainWelcomeController;
use App\Http\Controllers\MessagingController;

use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\StorageQuotaController;



use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// PWA Manifest and Service Worker routes
Route::get('/manifest.json', function () {
    return response()->file(public_path('manifest.json'), [
        'Content-Type' => 'application/manifest+json',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
})->name('pwa.manifest');

Route::get('/sw.js', function () {
    return response()->file(public_path('sw.js'), [
        'Content-Type' => 'application/javascript',
        'Service-Worker-Allowed' => '/',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
})->name('pwa.sw');

// CSRF Token Refresh Route (for preventing 419 errors)
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf-token');

// Session Activity & Auto-Logout Routes
// Config endpoint is public (no auth required) so JavaScript can load it
Route::get('/session/config', [App\Http\Controllers\SessionActivityController::class, 'getConfig'])->name('session.config');

// Protected session routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::post('/session/keep-alive', [App\Http\Controllers\SessionActivityController::class, 'keepAlive'])->name('session.keep-alive');
    Route::get('/session/status', [App\Http\Controllers\SessionActivityController::class, 'checkStatus'])->name('session.status');
    Route::post('/session/auto-logout', [App\Http\Controllers\SessionActivityController::class, 'autoLogout'])->name('session.auto-logout');
});





// Single entry point: redirect root to unified login
Route::get('/', function () { return redirect()->route('login'); })->name('main.welcome');

// Clinic Registration Routes (Public)
Route::get('/register-clinic', [App\Http\Controllers\ClinicRegistrationController::class, 'showRegistrationForm'])->name('clinic-registration.form');
Route::post('/register-clinic', [App\Http\Controllers\ClinicRegistrationController::class, 'register'])->name('clinic-registration.register');
Route::get('/registration-success', [App\Http\Controllers\ClinicRegistrationController::class, 'success'])->name('clinic-registration.success');
Route::get('/check-registration-status', [App\Http\Controllers\ClinicRegistrationController::class, 'showStatusForm'])->name('clinic-registration.check-status');
Route::post('/check-registration-status', [App\Http\Controllers\ClinicRegistrationController::class, 'status'])->name('clinic-registration.status');

// Unified access: route old paths to the single login
Route::get('/clinic', function () { return redirect()->route('login'); })->name('welcome.index');
Route::get('/register', function () { abort(404); })->name('welcome.register');
Route::post('/register', function () { abort(404); })->name('welcome.store');

// Master Control routes removed - application now managed by admin only

// Unified Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Handle GET requests to logout (redirect to login with message)
Route::get('/logout', function () {
    return redirect()->route('login')->with('info', 'Please use the logout button to sign out.');
});
// Backward compatibility redirects
Route::get('/auth/login', function () { return redirect()->route('login'); });
Route::post('/auth/login', function () { return redirect('/login'); });
Route::post('/auth/logout', function () { return redirect('/'); });

// Contract Review Routes (must be accessible even if contract pending)
Route::middleware('auth')->prefix('contract')->name('contract.')->group(function () {
    Route::get('/review', [App\Http\Controllers\ContractController::class, 'show'])->name('show');
    Route::post('/accept', [App\Http\Controllers\ContractController::class, 'accept'])->name('accept');
    Route::get('/view', [App\Http\Controllers\ContractController::class, 'view'])->name('view');
});

// Public clinic activation routes
Route::get('/activate-clinic', [ClinicActivationController::class, 'showActivationForm'])->name('clinic.activate.form');
Route::post('/activate-clinic', [ClinicActivationController::class, 'activate'])->name('clinic.activate');
Route::post('/validate-activation-code', [ClinicActivationController::class, 'validateCode'])->name('clinic.validate-code');

// Public invoice access (for patients via email links)
Route::get('/invoice/{invoice}/pdf/{token}', [FinanceController::class, 'publicInvoicePDF'])->name('finance.invoices.public.pdf');
Route::get('/invoice/{invoice}/view/{token}', [FinanceController::class, 'publicInvoiceView'])->name('finance.invoices.public.view');

// Public signed receipt URLs (encoded in receipt QR codes).
// Patients can scan the QR to view a read-only summary of their receipt.
Route::middleware('signed')->prefix('r')->name('public.receipt.')->group(function () {
    Route::get('/visit/{visit}', [App\Http\Controllers\PublicReceiptController::class, 'showVisit'])->name('visit');
    Route::get('/appointment/{appointment}', [App\Http\Controllers\PublicReceiptController::class, 'showAppointment'])->name('appointment');
    Route::get('/dental-treatment/{dentalTreatment}', [App\Http\Controllers\PublicReceiptController::class, 'showDentalTreatment'])->name('dental-treatment');
    Route::get('/orthodontic-case/{orthodonticCase}', [App\Http\Controllers\PublicReceiptController::class, 'showOrthodonticCase'])->name('orthodontic-case');
    Route::get('/medicine-sale/{invoice}', [App\Http\Controllers\PublicReceiptController::class, 'showMedicineSale'])->name('medicine-sale');
    Route::get('/prescription/{prescription}', [App\Http\Controllers\PublicReceiptController::class, 'showPrescription'])->name('prescription');
    Route::get('/aesthetic-invoice/{aestheticInvoice}', [App\Http\Controllers\PublicReceiptController::class, 'showAestheticInvoice'])->name('aesthetic-invoice');
});

// Diagnostic route for production debugging
Route::get('/finance-debug', function () {
    try {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'auth_check' => auth()->check(),
            'user_id' => auth()->id(),
            'environment' => app()->environment(),
            'app_debug' => config('app.debug'),
            'middleware_applied' => 'auth and can:manage-finance should be applied to finance routes',
            'git_commit' => trim(shell_exec('git rev-parse HEAD 2>/dev/null') ?: 'unknown'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});

// Clinic activation instructions
Route::get('/clinic-activation-guide', function () {
    return view('public.clinic-activation-instructions');
})->name('clinic.activation.guide');

// Public clinic registration request (Legacy - can be removed if not needed)
Route::get('/register-clinic', [ClinicActivationController::class, 'showRegistrationForm'])->name('clinic.register.form');
Route::post('/register-clinic', [ClinicActivationController::class, 'requestRegistration'])->name('clinic.register');

// Legacy registration routes moved to /auth/register
Route::get('/auth/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/auth/register', [RegisterController::class, 'register']);

// Language switching
Route::get('/language/{language}', [LanguageController::class, 'switch'])->name('language.switch');

// Test Kurdish PDF route
Route::get('/test-kurdish-pdf', function () {
    $pdf = Pdf::loadView('test-kurdish-pdf');

    // Configure PDF for Kurdish font
    $pdf->getDomPDF()->getOptions()->set('fontDir', storage_path('fonts'));
    $pdf->getDomPDF()->getOptions()->set('fontCache', storage_path('fonts'));
    $pdf->getDomPDF()->getOptions()->set('defaultFont', 'amiri-regular');

    return $pdf->download('kurdish-font-test.pdf');
});

// Debug Kurdish text processing
Route::get('/debug-kurdish', function () {
    $arabic = new \ArPHP\I18N\Arabic();

    $testTexts = [
        'ماسی سەلمۆن',
        'برنجی قاوەیی',
        'سنگی مریشک',
        'زەڵاتەی ئیسپانەخ'
    ];

    $results = [];
    foreach ($testTexts as $text) {
        $processed = $arabic->utf8Glyphs($text);
        $results[] = [
            'original' => $text,
            'processed' => $processed,
            'same' => $text === $processed ? 'YES' : 'NO',
            'length_original' => mb_strlen($text),
            'length_processed' => mb_strlen($processed)
        ];
    }

    return response()->json($results);
});

// Activation and subscription status pages
Route::get('/activation-required', function () {
    return view('auth.activation-required');
})->name('activation.required');

Route::get('/clinic-activation-required', function () {
    return view('auth.clinic-activation-required');
})->name('clinic.activation.required');


// Fallback for legacy subscription status route referenced in old layouts
// Safe no-op endpoint to avoid 500s if a view calls route('subscription.status')
Route::get('/subscription/status', function () {
    return response()->json(['status' => 'ok']);
})->name('subscription.status');

// Subscription system removed - no longer needed

// Protected routes
Route::middleware(['auth', 'activation'])->group(function () {

    // Tenant Dashboard (Clinic Users Only)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Storage Quota (JSON API for AJAX calls)
    Route::get('/storage/info', [StorageQuotaController::class, 'getStorageInfo'])->name('storage.info');

    // Patient Management
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/create', [PatientController::class, 'create'])->name('create');
        Route::post('/', [PatientController::class, 'store'])->name('store');

        // Import/Export routes (must be before parameterized routes)
        Route::get('/import', [PatientController::class, 'showImport'])->name('import');
        Route::post('/import', [PatientController::class, 'import'])->name('import.process');
        Route::get('/import/template', [PatientController::class, 'downloadTemplate'])->name('import.template');
        Route::get('/export', [PatientController::class, 'export'])->name('export')->middleware('export.check');

        // Bulk operations (must be before parameterized routes)
        Route::post('/bulk-delete', [PatientController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/clear-all', [PatientController::class, 'clearAll'])->name('clear-all');

        // API route for dropdowns (must be before parameterized routes)
        Route::get('/api', [PatientController::class, 'apiList'])->name('api');
        Route::post('/{patient}/modules', [App\Http\Controllers\PatientModuleController::class, 'store'])->name('modules.store');
        Route::delete('/{patient}/modules/{module}', [App\Http\Controllers\PatientModuleController::class, 'destroy'])->name('modules.destroy');
        Route::get('/{patient}/modules/{module}', [App\Http\Controllers\PatientModuleController::class, 'show'])->name('modules.show');
        Route::put('/{patient}/medical-overview', [App\Http\Controllers\PatientMedicalOverviewController::class, 'update'])->name('medical-overview.update');
        Route::post('/{patient}/medications', [App\Http\Controllers\PatientMedicationController::class, 'store'])->name('medications.store');
        Route::post('/{patient}/visits', [App\Http\Controllers\PatientVisitController::class, 'store'])->name('visits.store');
        Route::get('/{patient}/visits/{visit}', [App\Http\Controllers\PatientVisitController::class, 'show'])->name('visits.show');
        Route::get('/{patient}/visits/{visit}/receipt', [App\Http\Controllers\ReceiptController::class, 'printVisit'])->name('visits.receipt');
        Route::get('/{patient}/dental', [App\Http\Controllers\PatientDentalController::class, 'show'])->name('dental.show');
        Route::put('/{patient}/dental', [App\Http\Controllers\PatientDentalController::class, 'update'])->name('dental.update');
        Route::middleware(['module:ent', 'section:ent'])->group(function () {
            Route::get('/{patient}/ent', [App\Http\Controllers\PatientEntController::class, 'show'])->name('ent.show');
            Route::put('/{patient}/ent', [App\Http\Controllers\PatientEntController::class, 'update'])->name('ent.update');
        });
        Route::get('/{patient}/pediatric', [App\Http\Controllers\PatientPediatricController::class, 'show'])->name('pediatric.show');
        Route::put('/{patient}/pediatric', [App\Http\Controllers\PatientPediatricController::class, 'update'])->name('pediatric.update');
        Route::get('/{patient}/nutrition', [App\Http\Controllers\PatientNutritionController::class, 'show'])->name('nutrition.show');
        Route::put('/{patient}/nutrition', [App\Http\Controllers\PatientNutritionController::class, 'update'])->name('nutrition.update');
        Route::get('/{patient}/aesthetic', [App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'patientShow'])->name('aesthetic.show');
        Route::get('/{patient}/visit-timeline', [PatientController::class, 'visitTimeline'])->name('visit-timeline');

        Route::get('/{patient}', [PatientController::class, 'show'])->name('show');
        Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::put('/{patient}', [PatientController::class, 'update'])->name('update');
        Route::delete('/{patient}', [PatientController::class, 'destroy'])->name('destroy');

        // Patient specific routes
        Route::get('/{patient}/history', [PatientController::class, 'history'])->name('history');
        Route::post('/{patient}/checkup', [PatientController::class, 'addCheckup'])->name('checkup');
        Route::post('/{patient}/upload', [PatientController::class, 'uploadFile'])->name('upload');


        // Patient Files (update description, delete)
        Route::patch('/{patient}/files/{file}', [PatientController::class, 'updateFile'])->name('files.update');
        Route::delete('/{patient}/files/{file}', [PatientController::class, 'destroyFile'])->name('files.destroy');

        // Patient Images
        Route::post('/{patient}/images', [App\Http\Controllers\PatientImageController::class, 'store'])->name('images.store');

        Route::patch('/{patient}/images/{image}', [App\Http\Controllers\PatientImageController::class, 'update'])->name('images.update');
        Route::delete('/{patient}/images/{image}', [App\Http\Controllers\PatientImageController::class, 'destroy'])->name('images.destroy');

        // Patient Videos (direct upload to Spaces)
        Route::post('/{patient}/videos/presigned-url', [App\Http\Controllers\PatientVideoController::class, 'presignedUrl'])->name('videos.presigned-url');
        Route::post('/{patient}/videos/upload', [App\Http\Controllers\PatientVideoController::class, 'upload'])->name('videos.upload');
        Route::post('/{patient}/videos', [App\Http\Controllers\PatientVideoController::class, 'store'])->name('videos.store');
        Route::get('/{patient}/videos/{video}', [App\Http\Controllers\PatientVideoController::class, 'show'])->name('videos.show');
        Route::patch('/{patient}/videos/{video}', [App\Http\Controllers\PatientVideoController::class, 'update'])->name('videos.update');
        Route::delete('/{patient}/videos/{video}', [App\Http\Controllers\PatientVideoController::class, 'destroy'])->name('videos.destroy');
    });

    Route::get('/patient/{patient}/dental', [App\Http\Controllers\PatientDentalController::class, 'show'])->name('patient.dental');
    Route::get('/patient/{patient}/ent', [App\Http\Controllers\PatientEntController::class, 'show'])
        ->middleware(['module:ent', 'section:ent'])
        ->name('patient.ent');
    Route::get('/patient/{patient}/pediatric', [App\Http\Controllers\PatientPediatricController::class, 'show'])->name('patient.pediatric');
    Route::get('/patient/{patient}/nutrition', [App\Http\Controllers\PatientNutritionController::class, 'show'])->name('patient.nutrition');

    // Checkup Management
    Route::prefix('patients/{patient}/checkups')->name('checkups.')->group(function () {
        Route::get('/', [App\Http\Controllers\CheckupController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\CheckupController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\CheckupController::class, 'store'])->name('store');
        Route::get('/{checkup}', [App\Http\Controllers\CheckupController::class, 'show'])->name('show');
        Route::get('/{checkup}/edit', [App\Http\Controllers\CheckupController::class, 'edit'])->name('edit');
        Route::put('/{checkup}', [App\Http\Controllers\CheckupController::class, 'update'])->name('update');
        Route::delete('/{checkup}', [App\Http\Controllers\CheckupController::class, 'destroy'])->name('destroy');
    });

    // Patient Reports
    Route::get('/patients/{patient}/report', [App\Http\Controllers\PatientReportController::class, 'generateReport'])->name('patient.report');
    Route::get('/patients/{patient}/blank-report', [App\Http\Controllers\PatientReportController::class, 'showBlankReportForm'])->name('patient.blank-report');
    Route::post('/patients/{patient}/blank-report/preview', [App\Http\Controllers\PatientReportController::class, 'previewBlankReport'])->name('patient.blank-report.preview');
    Route::post('/patients/{patient}/blank-report', [App\Http\Controllers\PatientReportController::class, 'generateBlankReport'])->name('patient.blank-report.generate');

    // Report Templates (AJAX)
    Route::post('/report-templates', [App\Http\Controllers\PatientReportController::class, 'storeTemplate'])->name('report-templates.store');
    Route::put('/report-templates/{report_template}', [App\Http\Controllers\PatientReportController::class, 'updateTemplate'])->name('report-templates.update');
    Route::delete('/report-templates/{report_template}', [App\Http\Controllers\PatientReportController::class, 'destroyTemplate'])->name('report-templates.destroy');


    // Medical Image Bank (top-level)
    Route::get('/image-bank', [App\Http\Controllers\ImageBankController::class, 'index'])->name('image-bank.index');
    Route::delete('/image-bank/{patientImage}', [App\Http\Controllers\ImageBankController::class, 'destroy'])->name('image-bank.destroy');

        // AI Medical Assistant (top-level)
        Route::prefix('ai-assistant')->name('assistant.')->group(function () {
            Route::get('/', [AssistantController::class, 'index'])->name('index');
            Route::post('/accept', [AssistantController::class, 'acceptDisclaimer'])->name('accept');
            Route::post('/send', [AssistantController::class, 'send'])->name('send');
            Route::delete('/clear', [AssistantController::class, 'clearHistory'])->name('clear');
            Route::get('/export-pdf', [AssistantController::class, 'exportPdf'])->name('export-pdf');
        });


    // Patient Vital Signs Management
    Route::prefix('patients/{patient}/vital-signs')->name('patients.vital-signs.')->group(function () {
        Route::get('/', [App\Http\Controllers\PatientVitalSignsController::class, 'index'])->name('index');
        Route::post('/assign', [App\Http\Controllers\PatientVitalSignsController::class, 'assign'])->name('assign');
        Route::post('/assign-template', [App\Http\Controllers\PatientVitalSignsController::class, 'assignFromTemplate'])->name('assign-template');
        Route::patch('/{assignment}/toggle', [App\Http\Controllers\PatientVitalSignsController::class, 'toggle'])->name('toggle');
        Route::put('/{assignment}', [App\Http\Controllers\PatientVitalSignsController::class, 'update'])->name('update');
        Route::delete('/{assignment}', [App\Http\Controllers\PatientVitalSignsController::class, 'destroy'])->name('destroy');
        Route::get('/available', [App\Http\Controllers\PatientVitalSignsController::class, 'getAvailableVitalSigns'])->name('available');
    });

    // Patient Checkup Templates Management
    Route::prefix('patients/{patient}/checkup-templates')->name('patients.checkup-templates.')->group(function () {
        Route::get('/', [App\Http\Controllers\PatientCheckupTemplateController::class, 'index'])->name('index');
        Route::post('/assign', [App\Http\Controllers\PatientCheckupTemplateController::class, 'assign'])->name('assign');
        Route::post('/assign-recommended', [App\Http\Controllers\PatientCheckupTemplateController::class, 'assignRecommended'])->name('assign-recommended');
        Route::patch('/{assignment}/toggle', [App\Http\Controllers\PatientCheckupTemplateController::class, 'toggle'])->name('toggle');
        Route::put('/{assignment}', [App\Http\Controllers\PatientCheckupTemplateController::class, 'update'])->name('update');
        Route::delete('/{assignment}', [App\Http\Controllers\PatientCheckupTemplateController::class, 'destroy'])->name('destroy');
        Route::get('/available', [App\Http\Controllers\PatientCheckupTemplateController::class, 'getAvailableTemplates'])->name('available');
        Route::get('/recommended', [App\Http\Controllers\PatientCheckupTemplateController::class, 'getRecommendedTemplates'])->name('recommended');
        Route::get('/{template}/preview', [App\Http\Controllers\PatientCheckupTemplateController::class, 'preview'])->name('preview');
    });

    // Patient Forms Management
    Route::prefix('patients/{patient}/forms')->name('patients.forms.')->group(function () {
        Route::get('/', [App\Http\Controllers\PatientFormController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\PatientFormController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\PatientFormController::class, 'store'])->name('store');
        // Fill/PDF routes must come before the catch-all /{patientForm}
        Route::get('/{patientForm}/fill', [App\Http\Controllers\PatientFormController::class, 'fill'])->name('fill');
        Route::post('/{patientForm}/fill', [App\Http\Controllers\PatientFormController::class, 'submitFill'])->name('fill.submit');
        Route::get('/{patientForm}/pdf', [App\Http\Controllers\PatientFormController::class, 'pdf'])->name('pdf');
        Route::get('/{patientForm}/attachment', [App\Http\Controllers\PatientFormController::class, 'attachment'])->name('attachment');
        Route::get('/{patientForm}/pdf-snapshot', [App\Http\Controllers\PatientFormController::class, 'pdfSnapshot'])->name('pdf-snapshot');

        Route::get('/{patientForm}', [App\Http\Controllers\PatientFormController::class, 'show'])->name('show');
        Route::delete('/{patientForm}', [App\Http\Controllers\PatientFormController::class, 'destroy'])->name('destroy');
    });


    // Custom Vital Signs Management (Admin only)
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        Route::resource('custom-vital-signs', App\Http\Controllers\CustomVitalSignsController::class);
        Route::patch('custom-vital-signs/{customVitalSign}/toggle-status', [App\Http\Controllers\CustomVitalSignsController::class, 'toggleStatus'])->name('custom-vital-signs.toggle-status');

        // Custom Checkup Templates Management
        Route::resource('checkup-templates', App\Http\Controllers\CustomCheckupTemplateController::class)
            ->parameters(['checkup-templates' => 'template']);
        Route::patch('checkup-templates/{template}/toggle-status', [App\Http\Controllers\CustomCheckupTemplateController::class, 'toggleStatus'])->name('checkup-templates.toggle-status');
        Route::post('checkup-templates/{template}/clone', [App\Http\Controllers\CustomCheckupTemplateController::class, 'clone'])->name('checkup-templates.clone');
        Route::get('checkup-templates/{template}/preview', [App\Http\Controllers\CustomCheckupTemplateController::class, 'preview'])->name('checkup-templates.preview');
        Route::get('checkup-templates/{template}/activity-summary', [App\Http\Controllers\CustomCheckupTemplateController::class, 'activitySummary'])->name('checkup-templates.activity-summary');

        // Test route for debugging
        Route::get('checkup-templates-test', function() {
            try {
                $checkupTypes = App\Models\CustomCheckupTemplate::getCheckupTypes();
                $fieldTypes = App\Models\CustomCheckupTemplate::getFieldTypes();
                $defaultTemplates = App\Models\CustomCheckupTemplate::getDefaultTemplates();
                return response()->json([
                    'status' => 'success',
                    'checkupTypes' => $checkupTypes,
                    'fieldTypes' => $fieldTypes,
                    'defaultTemplates' => count($defaultTemplates),
                    'user' => auth()->user()->email ?? 'not authenticated'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);
            }
        })->name('checkup-templates.test');
    });

    // Prescription Management (Original - Complex)
    Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
        Route::get('/', [PrescriptionController::class, 'index'])->name('index');
        Route::get('/create', [PrescriptionController::class, 'create'])->name('create');
        Route::post('/', [PrescriptionController::class, 'store'])->name('store');
        Route::get('/{prescription}', [PrescriptionController::class, 'show'])->name('show');
        Route::get('/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('edit');
        Route::put('/{prescription}', [PrescriptionController::class, 'update'])->name('update');
        Route::delete('/{prescription}', [PrescriptionController::class, 'destroy'])->name('destroy');
        Route::get('/{prescription}/pdf', [PrescriptionController::class, 'generatePDF'])->name('pdf');
    });

    // Simple Prescription Management (New - Clean & Simple)
    Route::prefix('simple-prescriptions')->name('simple-prescriptions.')->group(function () {
        Route::get('/', [SimplePrescriptionController::class, 'index'])->name('index');
        Route::get('/create', [SimplePrescriptionController::class, 'create'])->name('create');
        Route::get('/quick-visit', [SimplePrescriptionController::class, 'quickVisit'])->name('quick-visit')->middleware('module:quick_visit');
        Route::post('/', [SimplePrescriptionController::class, 'store'])->name('store');
        Route::get('/{prescription}', [SimplePrescriptionController::class, 'show'])->name('show');
        Route::get('/{prescription}/edit', [SimplePrescriptionController::class, 'edit'])->name('edit');
        Route::put('/{prescription}', [SimplePrescriptionController::class, 'update'])->name('update');
        Route::delete('/{prescription}', [SimplePrescriptionController::class, 'destroy'])->name('destroy');
        Route::get('/{prescription}/pdf', [SimplePrescriptionController::class, 'pdf'])->name('pdf');
        Route::get('/{prescription}/print', [SimplePrescriptionController::class, 'print'])->name('print');
        Route::get('/{prescription}/thermal', [SimplePrescriptionController::class, 'thermal'])->name('thermal');
        Route::post('/{prescription}/dispense', [SimplePrescriptionController::class, 'convertToSale'])->name('dispense');
    });

    // Prescription Template Preview (demo)
    Route::get('/prescription-template-preview', [SimplePrescriptionController::class, 'templatePreview'])->name('simple-prescriptions.template-preview');

    // Medicine Management
    Route::prefix('medicines')->name('medicines.')->group(function () {
        Route::get('/', [App\Http\Controllers\MedicineController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\MedicineController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\MedicineController::class, 'store'])->name('store');
        Route::get('/search', [App\Http\Controllers\MedicineController::class, 'search'])->name('search');

        // Import/Export routes
        Route::get('/import', [App\Http\Controllers\MedicineController::class, 'showImport'])->name('import');
        Route::post('/import', [App\Http\Controllers\MedicineController::class, 'import'])->name('import.process');
        Route::get('/import/template', [App\Http\Controllers\MedicineController::class, 'downloadTemplate'])->name('import.template');
        Route::get('/export', [App\Http\Controllers\MedicineController::class, 'export'])->name('export')->middleware('export.check');

        // Bulk operations
        Route::post('/bulk-delete', [App\Http\Controllers\MedicineController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/clear-all', [App\Http\Controllers\MedicineController::class, 'clearAll'])->name('clear-all');

        // Clinic-scoped custom forms (must be before {medicine} wildcards)
        Route::prefix('forms')->name('forms.')->group(function () {
            Route::get('/', [App\Http\Controllers\MedicineFormController::class, 'index'])->name('index');
            Route::put('/{medicineForm}', [App\Http\Controllers\MedicineFormController::class, 'update'])->name('update');
            Route::delete('/{medicineForm}', [App\Http\Controllers\MedicineFormController::class, 'destroy'])->name('destroy');
        });

        // Multi-item sale (must be declared before the {medicine} wildcard routes)
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/create', [App\Http\Controllers\MedicineSaleController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\MedicineSaleController::class, 'store'])->name('store');
            Route::get('/{invoice}', [App\Http\Controllers\MedicineSaleController::class, 'show'])->name('show');
            Route::get('/{invoice}/pdf', [App\Http\Controllers\MedicineSaleController::class, 'pdf'])->name('pdf');
            Route::get('/{invoice}/thermal', [App\Http\Controllers\MedicineSaleController::class, 'thermal'])->name('thermal');
        });

        // Sell and Purchase routes
        Route::get('/{medicine}/sell', [App\Http\Controllers\MedicineController::class, 'sellForm'])->name('sell');
        Route::post('/{medicine}/sell', [App\Http\Controllers\MedicineController::class, 'processSell'])->name('sell.process');
        Route::get('/{medicine}/purchase', [App\Http\Controllers\MedicineController::class, 'purchaseForm'])->name('purchase');
        Route::post('/{medicine}/purchase', [App\Http\Controllers\MedicineController::class, 'processPurchase'])->name('purchase.process');

        Route::get('/{medicine}', [App\Http\Controllers\MedicineController::class, 'show'])->name('show');
        Route::get('/{medicine}/edit', [App\Http\Controllers\MedicineController::class, 'edit'])->name('edit');
        Route::put('/{medicine}', [App\Http\Controllers\MedicineController::class, 'update'])->name('update');
        Route::delete('/{medicine}', [App\Http\Controllers\MedicineController::class, 'destroy'])->name('destroy');
        Route::patch('/{medicine}/toggle-status', [App\Http\Controllers\MedicineController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{medicine}/toggle-frequent', [App\Http\Controllers\MedicineController::class, 'toggleFrequent'])->name('toggle-frequent');
    });

    // External Labs Management (Admin only)
    Route::prefix('external-labs')->name('external-labs.')->group(function () {
        Route::get('/', [App\Http\Controllers\ExternalLabController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\ExternalLabController::class, 'store'])->name('store');
        Route::get('/{externalLab}', [App\Http\Controllers\ExternalLabController::class, 'show'])->name('show');
        Route::put('/{externalLab}', [App\Http\Controllers\ExternalLabController::class, 'update'])->name('update');
        Route::delete('/{externalLab}', [App\Http\Controllers\ExternalLabController::class, 'destroy'])->name('destroy');
        Route::patch('/{externalLab}/toggle-status', [App\Http\Controllers\ExternalLabController::class, 'toggleStatus'])->name('toggle-status');
    });



    // Appointment Management
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/create', [AppointmentController::class, 'create'])->name('create');
        // Static utility routes must come before the {appointment} wildcard so
        // they aren't captured as appointment ids.
        Route::get('/calendar-events', [AppointmentController::class, 'calendarEvents'])->name('calendar-events');
        // Sidebar badge count for doctor's upcoming appointments (today)
        Route::get('/pending-count', [AppointmentController::class, 'pendingCount'])->name('pending-count');
        // Upcoming summary for bell dropdown
        Route::get('/upcoming-summary', [AppointmentController::class, 'upcomingSummary'])->name('upcoming-summary');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::get('/{appointment}/receipt/pdf', [AppointmentController::class, 'generateReceiptPDF'])->name('receipt-pdf');
        Route::get('/{appointment}/receipt/thermal', [App\Http\Controllers\ReceiptController::class, 'printAppointment'])->name('receipt-thermal');
        Route::get('/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
        Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
        Route::patch('/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('update-status');
    });

    // Nutrition Progress Dashboard
    Route::prefix('nutrition/progress')->name('nutrition.progress.')->group(function () {
        Route::get('/', [App\Http\Controllers\NutritionProgressController::class, 'dashboard'])->name('dashboard');
        Route::post('/measurement', [App\Http\Controllers\NutritionProgressController::class, 'storeMeasurement'])->name('measurement.store');
        Route::delete('/measurement/{measurement}', [App\Http\Controllers\NutritionProgressController::class, 'destroyMeasurement'])->name('measurement.destroy');
        Route::post('/goal', [App\Http\Controllers\NutritionProgressController::class, 'storeGoal'])->name('goal.store');
        Route::get('/chart-data', [App\Http\Controllers\NutritionProgressController::class, 'chartData'])->name('chart-data');
    });

    // Nutrition Plan Management
    Route::prefix('nutrition')->name('nutrition.')->group(function () {
        Route::get('/', [App\Http\Controllers\NutritionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\NutritionController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\NutritionController::class, 'store'])->name('store');

        // Specialized nutrition plan templates (must be before parameterized routes)
        Route::get('/templates', [App\Http\Controllers\NutritionController::class, 'templates'])->name('templates');
        Route::get('/create/enhanced', [App\Http\Controllers\NutritionController::class, 'createEnhanced'])->name('create.enhanced');

        Route::get('/create/muscle-gain', [App\Http\Controllers\NutritionController::class, 'createMuscleGain'])->name('create.muscle-gain');
        Route::get('/create/diabetic', [App\Http\Controllers\NutritionController::class, 'createDiabetic'])->name('create.diabetic');
        Route::get('/create/flexible', [App\Http\Controllers\NutritionController::class, 'createFlexible'])->name('create.flexible');
        Route::post('/store-flexible', [App\Http\Controllers\NutritionController::class, 'storeFlexible'])->name('store-flexible');

        // Enhanced edit route (must be before parameterized routes)
        Route::get('/{dietPlan}/edit/enhanced', [App\Http\Controllers\NutritionController::class, 'editEnhanced'])->name('edit.enhanced');

        // Parameterized routes (must be after specific routes)
        Route::get('/{dietPlan}', [App\Http\Controllers\NutritionController::class, 'show'])->name('show');
        Route::get('/{dietPlan}/edit', [App\Http\Controllers\NutritionController::class, 'edit'])->name('edit');
        Route::put('/{dietPlan}', [App\Http\Controllers\NutritionController::class, 'update'])->name('update');
        Route::delete('/{dietPlan}', [App\Http\Controllers\NutritionController::class, 'destroy'])->name('destroy');
        Route::get('/{dietPlan}/pdf', [App\Http\Controllers\NutritionController::class, 'pdf'])->name('pdf');
        Route::get('/{dietPlan}/word', [App\Http\Controllers\NutritionController::class, 'downloadWord'])->name('word');


        // Calorie calculation API
        Route::post('/calculate-calories', [App\Http\Controllers\NutritionController::class, 'calculateTargetCalories'])->name('calculate-calories');
            // Auto-generate meal plan API
            Route::post('/auto-generate-plan', [App\Http\Controllers\NutritionController::class, 'autoGeneratePlan'])->name('auto-generate-plan');


        // Weight tracking routes
        Route::get('/{dietPlan}/weight-tracking', [App\Http\Controllers\NutritionController::class, 'weightTracking'])->name('weight-tracking');
        Route::post('/{dietPlan}/weight-records', [App\Http\Controllers\NutritionController::class, 'storeWeightRecord'])->name('weight-records.store');
        Route::put('/{dietPlan}/weight-records/{weightRecord}', [App\Http\Controllers\NutritionController::class, 'updateWeightRecord'])->name('weight-records.update');



        Route::delete('/{dietPlan}/weight-records/{weightRecord}', [App\Http\Controllers\NutritionController::class, 'deleteWeightRecord'])->name('weight-records.delete');
    });

    // Recommendations
    Route::prefix('recommendations')->name('recommendations.')->group(function () {
        Route::get('/', [RecommendationController::class, 'index'])->name('index');

        // Lab Requests
        Route::get('/lab-requests', [RecommendationController::class, 'labRequests'])->name('lab-requests');
        Route::post('/lab-requests', [RecommendationController::class, 'storeLabRequest'])->name('lab-requests.store');
        Route::get('/lab-requests/{labRequest}', [RecommendationController::class, 'showLabRequest'])->name('lab-requests.show');
        Route::get('/lab-requests/{labRequest}/edit', [RecommendationController::class, 'editLabRequest'])->name('lab-requests.edit');
        Route::put('/lab-requests/{labRequest}', [RecommendationController::class, 'updateLabRequest'])->name('lab-requests.update');
        Route::get('/lab-requests/{labRequest}/print', [RecommendationController::class, 'printLabRequest'])->name('lab-requests.print');
        Route::get('/lab-requests/{labRequest}/pdf', [RecommendationController::class, 'pdfLabRequest'])->name('lab-requests.pdf');

    // Forms Management
    Route::prefix('forms')->name('forms.')->group(function () {
        // Template management
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [FormTemplateController::class, 'index'])->name('index');
            Route::get('/create', [FormTemplateController::class, 'create'])->name('create');
            Route::post('/', [FormTemplateController::class, 'store'])->name('store');
            Route::get('/{formTemplate}/edit', [FormTemplateController::class, 'edit'])->name('edit');
            Route::put('/{formTemplate}', [FormTemplateController::class, 'update'])->name('update');
            Route::delete('/{formTemplate}', [FormTemplateController::class, 'destroy'])->name('destroy');
            Route::get('/{formTemplate}/download', [FormTemplateController::class, 'download'])->name('download');
        });
    });



        // Radiology Requests
        Route::prefix('radiology')->name('radiology.')->group(function () {
            Route::get('/', [App\Http\Controllers\RadiologyController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\RadiologyController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\RadiologyController::class, 'store'])->name('store');
            Route::get('/{radiologyRequest}', [App\Http\Controllers\RadiologyController::class, 'show'])->name('show');
            Route::get('/{radiologyRequest}/edit', [App\Http\Controllers\RadiologyController::class, 'edit'])->name('edit');
            Route::put('/{radiologyRequest}', [App\Http\Controllers\RadiologyController::class, 'update'])->name('update');
            Route::delete('/{radiologyRequest}', [App\Http\Controllers\RadiologyController::class, 'destroy'])->name('destroy');
            Route::get('/{radiologyRequest}/pdf', [App\Http\Controllers\RadiologyController::class, 'pdf'])->name('pdf');
            Route::patch('/{radiologyRequest}/status', [App\Http\Controllers\RadiologyController::class, 'updateStatus'])->name('update-status');
            Route::post('/{radiologyRequest}/upload-result', [App\Http\Controllers\RadiologyController::class, 'uploadResult'])->name('upload-result');

            // AJAX routes
            Route::get('/tests/by-category', [App\Http\Controllers\RadiologyController::class, 'getTestsByCategory'])->name('tests.by-category');
            Route::get('/tests/search', [App\Http\Controllers\RadiologyController::class, 'searchTests'])->name('tests.search');

            // Custom test management
            Route::post('/tests/create-custom', [App\Http\Controllers\RadiologyController::class, 'createCustomTest'])->name('tests.create-custom');
            Route::get('/tests/manage', [App\Http\Controllers\RadiologyController::class, 'manageTests'])->name('tests.manage');
            Route::delete('/tests/{radiologyTest}', [App\Http\Controllers\RadiologyController::class, 'deleteTest'])->name('tests.delete');
        });
        Route::patch('/lab-requests/{labRequest}/status', [RecommendationController::class, 'updateLabRequestStatus'])->name('lab-requests.update-status');
        Route::delete('/lab-requests/{labRequest}', [RecommendationController::class, 'destroyLabRequest'])->name('lab-requests.destroy');

        // Lab Request Communication
        Route::post('/lab-requests/{labRequest}/send-whatsapp', [App\Http\Controllers\LabRequestCommunicationController::class, 'sendViaWhatsApp'])->name('lab-requests.send-whatsapp');
        Route::post('/lab-requests/{labRequest}/send-email', [App\Http\Controllers\LabRequestCommunicationController::class, 'sendViaEmail'])->name('lab-requests.send-email');
        Route::post('/lab-requests/{labRequest}/upload-result', [App\Http\Controllers\LabRequestCommunicationController::class, 'uploadResult'])->name('lab-requests.upload-result');

        // Lab Technician Routes
        Route::prefix('lab-technician')->name('lab-technician.')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\LabTechnicianController::class, 'dashboard'])->name('dashboard');
            Route::get('/patients', [App\Http\Controllers\LabTechnicianController::class, 'patients'])->name('patients');
            Route::get('/patients/{patient}/files', [App\Http\Controllers\LabTechnicianController::class, 'showPatientFiles'])->name('patients.files');
            Route::post('/patients/{patient}/upload', [App\Http\Controllers\LabTechnicianController::class, 'uploadPatientFile'])->name('patients.upload');
        });

        // Radiology Technician Routes
        Route::prefix('radiology-technician')->name('radiology-technician.')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\RadiologyTechnicianController::class, 'dashboard'])->name('dashboard');
            Route::get('/patients', [App\Http\Controllers\RadiologyTechnicianController::class, 'patients'])->name('patients');
            Route::get('/patients/{patient}/files', [App\Http\Controllers\RadiologyTechnicianController::class, 'showPatientFiles'])->name('patients.files');
            Route::post('/patients/{patient}/upload', [App\Http\Controllers\RadiologyTechnicianController::class, 'uploadPatientFile'])->name('patients.upload');
        });

        // Debug endpoint for testing AJAX
        Route::get('/lab-requests/test-ajax', function() {
            return response()->json([
                'success' => true,
                'message' => 'AJAX is working!',
                'user' => auth()->user()->first_name ?? 'Unknown',
                'user_authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'timestamp' => now()->toDateTimeString()
            ]);
        })->name('lab-requests.test-ajax');

        // Simple auth test endpoint
        Route::get('/auth-test', function() {
            return response()->json([
                'authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->first_name ?? null,
                'session_id' => session()->getId(),
            ]);
        })->name('auth-test');

        // Direct lab request endpoint for testing
        Route::get('/lab-requests/{id}/direct', function($id) {
            $labRequest = App\Models\LabRequest::with(['patient', 'doctor', 'tests'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'labRequest' => $labRequest,
                'message' => 'Direct access working'
            ]);
        })->name('lab-requests.direct');



        // Prescriptions
        Route::get('/prescriptions', [RecommendationController::class, 'prescriptions'])->name('prescriptions');
        Route::post('/prescriptions', [RecommendationController::class, 'storePrescription'])->name('prescriptions.store');

        // Diet Plans
        Route::get('/diet-plans', [RecommendationController::class, 'dietPlans'])->name('diet-plans');
        Route::post('/diet-plans', [RecommendationController::class, 'storeDietPlan'])->name('diet-plans.store');
        Route::get('/diet-plans/{dietPlan}/pdf', [RecommendationController::class, 'generateDietPlanPDF'])->name('diet-plans.pdf');
    });

    // Dental Module Routes
    Route::prefix('dental')->name('dental.')->group(function () {
        // All Dental Charts (must be before patient-specific routes)
        Route::get('/charts', [App\Http\Controllers\DentalChartController::class, 'allCharts'])->name('charts.all');

        // Dental Charts
        Route::prefix('patients/{patient}/charts')->name('charts.')->group(function () {
            Route::get('/', [App\Http\Controllers\DentalChartController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\DentalChartController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\DentalChartController::class, 'store'])->name('store');
            Route::get('/{dentalChart}', [App\Http\Controllers\DentalChartController::class, 'show'])->name('show');
            Route::get('/{dentalChart}/edit', [App\Http\Controllers\DentalChartController::class, 'edit'])->name('edit');
            Route::put('/{dentalChart}', [App\Http\Controllers\DentalChartController::class, 'update'])->name('update');
            Route::delete('/{dentalChart}', [App\Http\Controllers\DentalChartController::class, 'destroy'])->name('destroy');
            Route::post('/{dentalChart}/tooth-record', [App\Http\Controllers\DentalChartController::class, 'updateToothRecord'])->name('update-tooth-record');
            Route::get('/{dentalChart}/pdf', [App\Http\Controllers\DentalChartController::class, 'pdf'])->name('pdf');
        });
        Route::get('/patients/{patient}/dental-history', [App\Http\Controllers\DentalChartController::class, 'history'])->name('history');

        // Dental Treatments
        Route::prefix('treatments')->name('treatments.')->group(function () {
            Route::get('/', [App\Http\Controllers\DentalTreatmentController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\DentalTreatmentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\DentalTreatmentController::class, 'store'])->name('store');
            Route::get('/{dentalTreatment}', [App\Http\Controllers\DentalTreatmentController::class, 'show'])->name('show');
            Route::get('/{dentalTreatment}/edit', [App\Http\Controllers\DentalTreatmentController::class, 'edit'])->name('edit');
            Route::put('/{dentalTreatment}', [App\Http\Controllers\DentalTreatmentController::class, 'update'])->name('update');
            Route::delete('/{dentalTreatment}', [App\Http\Controllers\DentalTreatmentController::class, 'destroy'])->name('destroy');
            Route::post('/{dentalTreatment}/complete', [App\Http\Controllers\DentalTreatmentController::class, 'markAsCompleted'])->name('complete');
            Route::get('/{dentalTreatment}/pdf', [App\Http\Controllers\DentalTreatmentController::class, 'pdf'])->name('pdf');
            Route::get('/{dentalTreatment}/receipt', [App\Http\Controllers\ReceiptController::class, 'printDentalTreatment'])->name('receipt');

            // Canal Treatment (Endodontic Worksheet)
            Route::get('/{dentalTreatment}/canals', [App\Http\Controllers\CanalTreatmentController::class, 'getWorksheet'])->name('canals.worksheet');
            Route::post('/{dentalTreatment}/canals', [App\Http\Controllers\CanalTreatmentController::class, 'store'])->name('canals.store');
            Route::delete('/canals/{canalTreatment}', [App\Http\Controllers\CanalTreatmentController::class, 'destroy'])->name('canals.destroy');
        });

        // Canal Treatment Patient History
        Route::get('/patients/{patient}/canal-history', [App\Http\Controllers\CanalTreatmentController::class, 'patientHistory'])->name('canal-history');

        // Standard canals lookup (for create form - no treatment needed)
        Route::get('/canals/standard/{toothNumber}', [App\Http\Controllers\CanalTreatmentController::class, 'getStandardCanals'])->name('canals.standard');

        // Dental Images
        Route::prefix('patients/{patient}/images')->name('images.')->group(function () {
            Route::get('/', [App\Http\Controllers\DentalImageController::class, 'index'])->name('index');
            Route::get('/upload', [App\Http\Controllers\DentalImageController::class, 'upload'])->name('upload');
            Route::post('/', [App\Http\Controllers\DentalImageController::class, 'store'])->name('store');
            Route::get('/{dentalImage}', [App\Http\Controllers\DentalImageController::class, 'show'])->name('show');
            Route::delete('/{dentalImage}', [App\Http\Controllers\DentalImageController::class, 'destroy'])->name('destroy');
            Route::post('/{dentalImage}/link-tooth', [App\Http\Controllers\DentalImageController::class, 'linkToTooth'])->name('link-tooth');
            Route::post('/{dentalImage}/update-metadata', [App\Http\Controllers\DentalImageController::class, 'updateMetadata'])->name('update-metadata');
        });

        // Dental Lab Requests
        Route::prefix('lab-requests')->name('lab-requests.')->group(function () {
            Route::get('/', [App\Http\Controllers\DentalLabRequestController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\DentalLabRequestController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\DentalLabRequestController::class, 'store'])->name('store');
            Route::get('/{labRequest}', [App\Http\Controllers\DentalLabRequestController::class, 'show'])->name('show');
            Route::get('/{labRequest}/edit', [App\Http\Controllers\DentalLabRequestController::class, 'edit'])->name('edit');
	            Route::post('/{labRequest}/complete', [App\Http\Controllers\DentalLabRequestController::class, 'markAsCompleted'])->name('complete');
            Route::put('/{labRequest}', [App\Http\Controllers\DentalLabRequestController::class, 'update'])->name('update');
            Route::delete('/{labRequest}', [App\Http\Controllers\DentalLabRequestController::class, 'destroy'])->name('destroy');
        });

        // Dental External Labs Management
        Route::prefix('external-labs')->name('external-labs.')->group(function () {
            Route::get('/', [App\Http\Controllers\DentalExternalLabController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\DentalExternalLabController::class, 'store'])->name('store');
            Route::get('/{dentalLab}', [App\Http\Controllers\DentalExternalLabController::class, 'show'])->name('show');
            Route::put('/{dentalLab}', [App\Http\Controllers\DentalExternalLabController::class, 'update'])->name('update');
            Route::delete('/{dentalLab}', [App\Http\Controllers\DentalExternalLabController::class, 'destroy'])->name('destroy');
            Route::patch('/{dentalLab}/toggle-status', [App\Http\Controllers\DentalExternalLabController::class, 'toggleStatus'])->name('toggle-status');
        });
    });

    // Orthodontics Module Routes
    Route::prefix('orthodontics')->name('orthodontics.')->group(function () {
        Route::get('/', [App\Http\Controllers\OrthodonticCaseController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\OrthodonticCaseController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\OrthodonticCaseController::class, 'store'])->name('store');
        Route::get('/{orthodonticCase}', [App\Http\Controllers\OrthodonticCaseController::class, 'show'])->name('show');
        Route::get('/{orthodonticCase}/invoice', [App\Http\Controllers\OrthodonticCaseController::class, 'invoice'])->name('invoice');
        Route::get('/{orthodonticCase}/receipt', [App\Http\Controllers\ReceiptController::class, 'printOrthodonticCase'])->name('receipt');
        Route::get('/{orthodonticCase}/edit', [App\Http\Controllers\OrthodonticCaseController::class, 'edit'])->name('edit');
        Route::put('/{orthodonticCase}', [App\Http\Controllers\OrthodonticCaseController::class, 'update'])->name('update');
        Route::delete('/{orthodonticCase}', [App\Http\Controllers\OrthodonticCaseController::class, 'destroy'])->name('destroy');

        // Visit Management
        Route::post('/{orthodonticCase}/visits', [App\Http\Controllers\OrthodonticCaseController::class, 'storeVisit'])->name('visits.store');
        Route::get('/{orthodonticCase}/visits/{visit}', [App\Http\Controllers\OrthodonticCaseController::class, 'getVisit'])->name('visits.show');

        // Photo Management
        Route::post('/{orthodonticCase}/photos', [App\Http\Controllers\OrthodonticCaseController::class, 'storePhoto'])->name('photos.store');
        Route::delete('/{orthodonticCase}/photos/{photo}', [App\Http\Controllers\OrthodonticCaseController::class, 'deletePhoto'])->name('photos.destroy');

        // Payment Management
        Route::post('/{orthodonticCase}/payments', [App\Http\Controllers\OrthodonticCaseController::class, 'storePayment'])->name('payments.store');

        // Tooth Chart Management
        Route::post('/{orthodonticCase}/tooth-chart', [App\Http\Controllers\OrthodonticCaseController::class, 'updateToothChart'])->name('tooth-chart.update');

        // Treatment Phase Management
        Route::post('/{orthodonticCase}/phase', [App\Http\Controllers\OrthodonticCaseController::class, 'updatePhase'])->name('phase.update');
    });

    // Surgical Module Routes
    Route::prefix('surgery')->name('surgery.')->middleware(['module:surgery', 'section:surgery'])->group(function () {
        Route::get('/', [App\Http\Controllers\SurgicalCaseController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\SurgicalCaseController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\SurgicalCaseController::class, 'store'])->name('store');
        Route::get('/{surgicalCase}', [App\Http\Controllers\SurgicalCaseController::class, 'show'])->name('show');
        Route::get('/{surgicalCase}/edit', [App\Http\Controllers\SurgicalCaseController::class, 'edit'])->name('edit');
        Route::put('/{surgicalCase}', [App\Http\Controllers\SurgicalCaseController::class, 'update'])->name('update');
        Route::delete('/{surgicalCase}', [App\Http\Controllers\SurgicalCaseController::class, 'destroy'])->name('destroy');

        // Operations: pre-op, operative note, post-op
        Route::get('/{surgicalCase}/operations/create', [App\Http\Controllers\SurgicalCaseController::class, 'createOperation'])
            ->name('operations.create');
        Route::post('/{surgicalCase}/operations', [App\Http\Controllers\SurgicalCaseController::class, 'storeOperation'])
            ->name('operations.store');
        Route::get('/{surgicalCase}/operations/{operation}/edit', [App\Http\Controllers\SurgicalCaseController::class, 'editOperation'])
            ->name('operations.edit');
        Route::put('/{surgicalCase}/operations/{operation}', [App\Http\Controllers\SurgicalCaseController::class, 'updateOperation'])
            ->name('operations.update');

        // Surgical Visits: post-operative follow-ups
        Route::get('/{surgicalCase}/visits/create', [App\Http\Controllers\SurgicalVisitController::class, 'create'])
            ->name('visit.create');
        Route::post('/{surgicalCase}/visits', [App\Http\Controllers\SurgicalVisitController::class, 'store'])
            ->name('visit.store');
        Route::get('/{surgicalCase}/visits/{visit}/edit', [App\Http\Controllers\SurgicalVisitController::class, 'edit'])
            ->name('visit.edit');
        Route::put('/{surgicalCase}/visits/{visit}', [App\Http\Controllers\SurgicalVisitController::class, 'update'])
            ->name('visit.update');
    });

    // Aesthetic Treatments Routes
    Route::prefix('aesthetic/treatments')->name('aesthetic.treatments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Aesthetic\AestheticTreatmentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Aesthetic\AestheticTreatmentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Aesthetic\AestheticTreatmentController::class, 'store'])->name('store');
        Route::get('/{aestheticTreatment}/edit', [\App\Http\Controllers\Aesthetic\AestheticTreatmentController::class, 'edit'])->name('edit');
        Route::put('/{aestheticTreatment}', [\App\Http\Controllers\Aesthetic\AestheticTreatmentController::class, 'update'])->name('update');
        Route::delete('/destroy-all', [\App\Http\Controllers\Aesthetic\AestheticTreatmentController::class, 'destroyAll'])->name('destroyAll');
        Route::delete('/{aestheticTreatment}', [\App\Http\Controllers\Aesthetic\AestheticTreatmentController::class, 'destroy'])->name('destroy');
    });

    // Aesthetic Packages Routes
    Route::prefix('aesthetic/packages')->name('aesthetic.packages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Aesthetic\AestheticPackageController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Aesthetic\AestheticPackageController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Aesthetic\AestheticPackageController::class, 'store'])->name('store');
        Route::get('/{aestheticPackage}/edit', [\App\Http\Controllers\Aesthetic\AestheticPackageController::class, 'edit'])->name('edit');
        Route::put('/{aestheticPackage}', [\App\Http\Controllers\Aesthetic\AestheticPackageController::class, 'update'])->name('update');
        Route::delete('/{aestheticPackage}', [\App\Http\Controllers\Aesthetic\AestheticPackageController::class, 'destroy'])->name('destroy');
    });

    // Aesthetic Patient Packages Routes
    Route::prefix('aesthetic/patient-packages')->name('aesthetic.patient-packages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Aesthetic\AestheticPatientPackageController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Aesthetic\AestheticPatientPackageController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Aesthetic\AestheticPatientPackageController::class, 'store'])->name('store');
        Route::get('/{patientPackage}/edit', [\App\Http\Controllers\Aesthetic\AestheticPatientPackageController::class, 'edit'])->name('edit');
        Route::put('/{patientPackage}', [\App\Http\Controllers\Aesthetic\AestheticPatientPackageController::class, 'update'])->name('update');
        Route::delete('/{patientPackage}', [\App\Http\Controllers\Aesthetic\AestheticPatientPackageController::class, 'destroy'])->name('destroy');
        Route::post('/{patientPackage}/use-session', [\App\Http\Controllers\Aesthetic\AestheticPatientPackageController::class, 'useSession'])->name('use-session');
    });

    // Aesthetic Sessions Routes
    Route::prefix('aesthetic/sessions')->name('aesthetic.sessions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'store'])->name('store');
        Route::get('/follow-up-reminders', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'followUpReminders'])->name('follow-up-reminders');
        Route::get('/{aestheticSession}', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'show'])->name('show');
        Route::post('/{aestheticSession}/send-whatsapp-reminder', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'sendWhatsAppReminder'])->name('send-whatsapp-reminder');
        Route::get('/{aestheticSession}/edit', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'edit'])->name('edit');
        Route::put('/{aestheticSession}', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'update'])->name('update');
        Route::delete('/{aestheticSession}', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'destroy'])->name('destroy');
        Route::post('/{aestheticSession}/consent', [\App\Http\Controllers\Aesthetic\AestheticConsentController::class, 'store'])->name('consent.store');
        Route::post('/{aestheticSession}/aftercare', [\App\Http\Controllers\Aesthetic\AestheticAftercareIssueController::class, 'store'])->name('aftercare.store');
        Route::post('/{aestheticSession}/aftercare/{aestheticAftercareIssue}/send-whatsapp', [\App\Http\Controllers\Aesthetic\AestheticAftercareIssueController::class, 'sendViaWhatsApp'])->name('aftercare.send-whatsapp');
        Route::post('/{aestheticSession}/images', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'uploadImages'])->name('images.store');
        Route::delete('/{aestheticSession}/images/{sessionImage}', [\App\Http\Controllers\Aesthetic\AestheticSessionController::class, 'deleteImage'])->name('images.destroy');
    });

    // Aesthetic Aftercare Template Routes
    Route::prefix('aesthetic/settings/aftercare-templates')->name('aesthetic.aftercare-templates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Aesthetic\AestheticAftercareTemplateController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Aesthetic\AestheticAftercareTemplateController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Aesthetic\AestheticAftercareTemplateController::class, 'store'])->name('store');
        Route::get('/{aestheticAftercareTemplate}/edit', [\App\Http\Controllers\Aesthetic\AestheticAftercareTemplateController::class, 'edit'])->name('edit');
        Route::put('/{aestheticAftercareTemplate}', [\App\Http\Controllers\Aesthetic\AestheticAftercareTemplateController::class, 'update'])->name('update');
        Route::delete('/{aestheticAftercareTemplate}', [\App\Http\Controllers\Aesthetic\AestheticAftercareTemplateController::class, 'destroy'])->name('destroy');
    });

    // Aesthetic Inventory Routes
    Route::prefix('aesthetic/inventory')->name('aesthetic.inventory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Aesthetic\AestheticInventoryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Aesthetic\AestheticInventoryController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Aesthetic\AestheticInventoryController::class, 'store'])->name('store');
        Route::get('/{aestheticInventory}/edit', [\App\Http\Controllers\Aesthetic\AestheticInventoryController::class, 'edit'])->name('edit');
        Route::put('/{aestheticInventory}', [\App\Http\Controllers\Aesthetic\AestheticInventoryController::class, 'update'])->name('update');
        Route::delete('/{aestheticInventory}', [\App\Http\Controllers\Aesthetic\AestheticInventoryController::class, 'destroy'])->name('destroy');
        Route::post('/{aestheticInventory}/adjust-stock', [\App\Http\Controllers\Aesthetic\AestheticInventoryController::class, 'adjustStock'])->name('adjust-stock');
    });

    // Aesthetic Invoice Routes
    Route::prefix('aesthetic/invoices')->name('aesthetic.invoices.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'store'])->name('store');
        Route::get('/{aestheticInvoice}', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'show'])->name('show');
        Route::get('/{aestheticInvoice}/edit', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'edit'])->name('edit');
        Route::put('/{aestheticInvoice}', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'update'])->name('update');
        Route::delete('/{aestheticInvoice}', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'destroy'])->name('destroy');
        Route::get('/{aestheticInvoice}/receipt', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'receipt'])->name('receipt');
        Route::get('/{aestheticInvoice}/invoice', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'invoice'])->name('invoice');
        Route::get('/{aestheticInvoice}/thermal-receipt', [App\Http\Controllers\ReceiptController::class, 'printAestheticInvoice'])->name('thermal-receipt');
        Route::post('/{aestheticInvoice}/mark-paid', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'markAsPaid'])->name('mark-paid');
        Route::post('/{aestheticInvoice}/send', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'send'])->name('send');
        Route::post('/{aestheticInvoice}/cancel', [\App\Http\Controllers\Aesthetic\AestheticInvoiceController::class, 'cancel'])->name('cancel');
    });

    // ENT Module Routes
    Route::prefix('ent')->name('ent.')->middleware(['module:ent', 'section:ent'])->group(function () {
        // Audiometry Tests (MUST be before {entRecord} wildcard route)
        Route::prefix('audiometry')->name('audiometry.')->group(function () {
            Route::get('/', [App\Http\Controllers\AudiometryController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\AudiometryController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\AudiometryController::class, 'store'])->name('store');
            Route::get('/{audiometryTest}', [App\Http\Controllers\AudiometryController::class, 'show'])->name('show');
            Route::get('/{audiometryTest}/edit', [App\Http\Controllers\AudiometryController::class, 'edit'])->name('edit');
            Route::put('/{audiometryTest}', [App\Http\Controllers\AudiometryController::class, 'update'])->name('update');
        });

        // ENT Records (wildcard routes MUST be last)
        Route::get('/', [App\Http\Controllers\EntController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\EntController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\EntController::class, 'store'])->name('store');
        Route::get('/{entRecord}/print', [App\Http\Controllers\EntController::class, 'print'])->name('print');
        Route::get('/{entRecord}', [App\Http\Controllers\EntController::class, 'show'])->name('show');
        Route::get('/{entRecord}/edit', [App\Http\Controllers\EntController::class, 'edit'])->name('edit');
        Route::put('/{entRecord}', [App\Http\Controllers\EntController::class, 'update'])->name('update');
        Route::delete('/{entRecord}', [App\Http\Controllers\EntController::class, 'destroy'])->name('destroy');
    });

    // Pediatric Growth Chart Routes
    Route::prefix('pediatric')->name('pediatric.')->group(function () {
        Route::get('/patients', [App\Http\Controllers\GrowthChartController::class, 'patients'])->name('patients');
        Route::get('/patients/{patient}/growth-chart', [App\Http\Controllers\GrowthChartController::class, 'index'])->name('growth-chart');
        Route::post('/patients/{patient}/growth-chart', [App\Http\Controllers\GrowthChartController::class, 'store'])->name('growth-chart.store');
        Route::delete('/patients/{patient}/growth-chart/{measurement}', [App\Http\Controllers\GrowthChartController::class, 'destroy'])->name('growth-chart.destroy');
        Route::get('/patients/{patient}/growth-chart/data', [App\Http\Controllers\GrowthChartController::class, 'chartData'])->name('growth-chart.data');

        // Pediatric Medication Safety Routes
        Route::prefix('medication')->name('medication.')->group(function () {
            Route::get('/calculator', [App\Http\Controllers\PediatricMedicationController::class, 'calculator'])->name('calculator');
            Route::post('/calculate', [App\Http\Controllers\PediatricMedicationController::class, 'calculateDose'])->name('calculate');
            Route::post('/calculate-bulk', [App\Http\Controllers\PediatricMedicationController::class, 'bulkCalculate'])->name('calculate.bulk');
            Route::post('/validate', [App\Http\Controllers\PediatricMedicationController::class, 'validateDose'])->name('validate');
            Route::post('/prescribe', [App\Http\Controllers\PediatricMedicationController::class, 'storePrescription'])->name('prescribe');
            Route::post('/prescribe-bulk', [App\Http\Controllers\PediatricMedicationController::class, 'bulkPrescribe'])->name('prescribe.bulk');
            Route::get('/history', [App\Http\Controllers\PediatricMedicationController::class, 'history'])->name('history');
            Route::get('/print', [App\Http\Controllers\PediatricMedicationController::class, 'printPrescription'])->name('print');

            // Drug Admin
            Route::get('/drugs', [App\Http\Controllers\PediatricMedicationController::class, 'drugAdmin'])->name('drug-admin');
            Route::post('/drugs', [App\Http\Controllers\PediatricMedicationController::class, 'storeDrug'])->name('drug.store');
            Route::post('/drugs/form', [App\Http\Controllers\PediatricMedicationController::class, 'storeDrugForm'])->name('drug-form.store');
            Route::post('/drugs/rule', [App\Http\Controllers\PediatricMedicationController::class, 'storeDosageRule'])->name('dosage-rule.store');
            Route::delete('/drugs/{drug}', [App\Http\Controllers\PediatricMedicationController::class, 'destroyDrug'])->name('drug.destroy');
            Route::delete('/drugs-tenant/delete-all', [App\Http\Controllers\PediatricMedicationController::class, 'destroyTenantDrugs'])->name('drugs.destroy-tenant');

            // Import
            Route::get('/import', [App\Http\Controllers\PediatricMedicationController::class, 'importPage'])->name('import');
            Route::post('/import/preview', [App\Http\Controllers\PediatricMedicationController::class, 'importPreview'])->name('import.preview');
            Route::post('/import/confirm', [App\Http\Controllers\PediatricMedicationController::class, 'importConfirm'])->name('import.confirm');
            Route::get('/import/template', [App\Http\Controllers\PediatricMedicationController::class, 'downloadTemplate'])->name('import.template');

            // AJAX endpoints
            Route::get('/drug/{drug}/forms', [App\Http\Controllers\PediatricMedicationController::class, 'getDrugForms'])->name('drug.forms');
            Route::get('/patient/{patient}/info', [App\Http\Controllers\PediatricMedicationController::class, 'getPatientInfo'])->name('patient.info');
        });
    });

    // Vaccination Management Routes
    Route::prefix('vaccination')->name('vaccination.')->group(function () {
        Route::get('/', [App\Http\Controllers\VaccinationController::class, 'index'])->name('index');
        Route::post('/enroll', [App\Http\Controllers\VaccinationController::class, 'enroll'])->name('enroll');
        Route::get('/search-unenrolled', [App\Http\Controllers\VaccinationController::class, 'searchUnenrolled'])->name('search-unenrolled');
        Route::get('/patients/{patient}', [App\Http\Controllers\VaccinationController::class, 'show'])->name('show');
        Route::post('/patients/{patient}/generate', [App\Http\Controllers\VaccinationController::class, 'generate'])->name('generate');
        Route::post('/record/{vaccination}', [App\Http\Controllers\VaccinationController::class, 'record'])->name('record');
        Route::post('/skip/{vaccination}', [App\Http\Controllers\VaccinationController::class, 'skip'])->name('skip');
        Route::get('/patients/{patient}/print', [App\Http\Controllers\VaccinationController::class, 'printCard'])->name('print');
        Route::get('/patients/{patient}/api', [App\Http\Controllers\VaccinationController::class, 'apiPatientVaccinations'])->name('api.patient');

        // Admin routes
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [App\Http\Controllers\VaccinationController::class, 'adminIndex'])->name('index');
            Route::get('/schedule/{schedule}', [App\Http\Controllers\VaccinationController::class, 'adminShowSchedule'])->name('schedule.show');
            Route::post('/country', [App\Http\Controllers\VaccinationController::class, 'adminStoreCountry'])->name('country.store');
            Route::delete('/country/{country}', [App\Http\Controllers\VaccinationController::class, 'adminDestroyCountry'])->name('country.destroy');
            Route::post('/schedule', [App\Http\Controllers\VaccinationController::class, 'adminStoreSchedule'])->name('schedule.store');
            Route::post('/item', [App\Http\Controllers\VaccinationController::class, 'adminStoreItem'])->name('item.store');
            Route::post('/import-json', [App\Http\Controllers\VaccinationController::class, 'adminImportJson'])->name('import');
            Route::get('/vaccines', [App\Http\Controllers\VaccinationController::class, 'adminVaccines'])->name('vaccines');
            Route::post('/vaccines', [App\Http\Controllers\VaccinationController::class, 'adminStoreVaccine'])->name('vaccines.store');
        });
    });

    // Food Composition
    Route::prefix('foods')->name('foods.')->group(function () {
        Route::get('/', [FoodController::class, 'index'])->name('index');
        Route::get('/create', [FoodController::class, 'create'])->name('create');
        Route::post('/', [FoodController::class, 'store'])->name('store');

        // Import/Export routes must be before parameterized routes
        Route::get('/import', [FoodController::class, 'showImport'])->name('import');
        Route::post('/import', [FoodController::class, 'import'])->name('import.process');
        Route::get('/import/template', [FoodController::class, 'downloadTemplate'])->name('import.template');
        Route::get('/export', [FoodController::class, 'export'])->name('export')->middleware('export.check');

        // Search route must be before parameterized routes
        Route::get('/search', [FoodController::class, 'search'])->name('search');

        // Bulk operations must be before parameterized routes
        Route::post('/bulk-delete', [FoodController::class, 'bulkDelete'])->name('bulk-delete');
        Route::delete('/clear-all', [FoodController::class, 'clearAll'])->name('clear-all');

        // Parameterized routes (must be after specific routes)
        Route::get('/{food}', [FoodController::class, 'show'])->name('show');
        Route::get('/{food}/edit', [FoodController::class, 'edit'])->name('edit');
        Route::put('/{food}', [FoodController::class, 'update'])->name('update');
        Route::delete('/{food}', [FoodController::class, 'destroy'])->name('destroy');
        Route::post('/{food}/calculate-nutrition', [FoodController::class, 'calculateNutrition'])->name('calculate-nutrition');
    });

    // Food Groups
    Route::prefix('food-groups')->name('food-groups.')->group(function () {
        Route::get('/', [FoodGroupController::class, 'index'])->name('index');
        Route::get('/create', [FoodGroupController::class, 'create'])->name('create');
        Route::post('/', [FoodGroupController::class, 'store'])->name('store');
        Route::get('/{foodGroup}', [FoodGroupController::class, 'show'])->name('show');
        Route::get('/{foodGroup}/edit', [FoodGroupController::class, 'edit'])->name('edit');
        Route::put('/{foodGroup}', [FoodGroupController::class, 'update'])->name('update');
        Route::delete('/{foodGroup}', [FoodGroupController::class, 'destroy'])->name('destroy');
        Route::get('/api/list', [FoodGroupController::class, 'api'])->name('api');
    });

    // Finance Module
    Route::prefix('finance')->name('finance.')->middleware(['auth', 'can:manage-finance'])->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');

        // Invoices
        Route::get('/invoices', [FinanceController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/create', [FinanceController::class, 'createInvoice'])
            ->name('invoices.create')->middleware('can:finance-invoices-create');
        Route::post('/invoices', [FinanceController::class, 'storeInvoice'])->name('invoices.store');
        Route::get('/invoices/{invoice}/edit', [FinanceController::class, 'getInvoiceForEdit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [FinanceController::class, 'updateInvoice'])->name('invoices.update');
        Route::delete('/invoices/{invoice}', [FinanceController::class, 'destroyInvoice'])->name('invoices.destroy');
        Route::get('/invoices/{invoice}/pdf', [FinanceController::class, 'generateInvoicePDF'])->name('invoices.pdf');
        Route::get('/invoices/{invoice}/print', [FinanceController::class, 'printInvoice'])->name('invoices.print');
        Route::get('/invoices/{invoice}/public-pdf-url', [FinanceController::class, 'getPublicPdfUrl'])->name('invoices.public-pdf-url');
        Route::get('/invoices/{invoice}/email-form', [FinanceController::class, 'showEmailForm'])->name('invoices.email-form');
        Route::post('/invoices/{invoice}/email', [FinanceController::class, 'emailInvoice'])->name('invoices.email');
        Route::post('/invoices/{invoice}/mark-paid', [FinanceController::class, 'markInvoiceAsPaid'])->name('invoices.mark-paid');
        Route::post('/invoices/{invoice}/mark-sent', [FinanceController::class, 'markInvoiceAsSent'])->name('invoices.mark-sent');
        Route::post('/invoices/{invoice}/mark-cancelled', [FinanceController::class, 'markInvoiceAsCancelled'])->name('invoices.mark-cancelled');

        // Expenses - Diagnostic route
        Route::get('/expenses/debug', function() {
            try {
                $user = auth()->user();
                $debug = [
                    'user_id' => $user->id ?? 'null',
                    'user_email' => $user->email ?? 'null',
                    'clinic_id' => $user->clinic_id ?? 'null',
                    'can_access_finance' => $user->canAccessFinance() ?? 'error',
                    'expenses_table_exists' => Schema::hasTable('expenses'),
                    'expenses_count' => DB::table('expenses')->count(),
                ];

                // Try to query expenses
                try {
                    $expenses = \App\Models\Expense::with(['clinic', 'creator', 'approver'])
                        ->where('clinic_id', $user->clinic_id)
                        ->latest()
                        ->limit(5)
                        ->get();
                    $debug['expenses_query'] = 'success';
                    $debug['expenses_found'] = $expenses->count();
                } catch (\Exception $e) {
                    $debug['expenses_query'] = 'failed';
                    $debug['expenses_error'] = $e->getMessage();
                }

                return response()->json($debug);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString())
                ], 500);
            }
        })->name('expenses.debug');

        // Expenses
        Route::get('/expenses', [FinanceController::class, 'expenses'])->name('expenses');
        Route::get('/expenses/create', [FinanceController::class, 'createExpense'])
            ->name('expenses.create')->middleware('can:finance-expenses-create');
        Route::post('/expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');
        Route::put('/expenses/{expense}', [FinanceController::class, 'updateExpense'])->name('expenses.update');
        Route::post('/expenses/{expense}/approve', [FinanceController::class, 'approveExpense'])->name('expenses.approve');
        Route::post('/expenses/{expense}/reject', [FinanceController::class, 'rejectExpense'])->name('expenses.reject');
        Route::delete('/expenses/{expense}', [FinanceController::class, 'destroyExpense'])->name('expenses.destroy');

        // Receipts
        Route::get('/receipts', [FinanceController::class, 'receipts'])->name('receipts');
        Route::get('/receipts/create', [FinanceController::class, 'createReceipt'])
            ->name('receipts.create')->middleware('can:finance-receipts-create');
        Route::post('/receipts', [FinanceController::class, 'storeReceipt'])->name('receipts.store');
        Route::put('/receipts/{receipt}', [FinanceController::class, 'updateReceipt'])->name('receipts.update');
        Route::delete('/receipts/{receipt}', [FinanceController::class, 'destroyReceipt'])->name('receipts.destroy');

        // Reports (strict: require finance_reports permission)
        Route::get('/reports', [FinanceController::class, 'reports'])->name('reports')->middleware('can:finance-reports');
        Route::get('/reports/cash-flow', [FinanceController::class, 'cashFlowReport'])->name('reports.cash-flow')->middleware('can:finance-reports');
        Route::get('/reports/profit-loss', [FinanceController::class, 'profitLossReport'])->name('reports.profit-loss')->middleware('can:finance-reports');
        Route::get('/reports/user-performance', [FinanceController::class, 'userPerformanceReport'])->name('reports.user-performance')->middleware('can:finance-reports');
    });

    // Advertisements
    Route::prefix('advertisements')->name('advertisements.')->group(function () {
        Route::get('/', [AdvertisementController::class, 'index'])->name('index');
        Route::get('/create', [AdvertisementController::class, 'create'])->name('create');
        Route::post('/', [AdvertisementController::class, 'store'])->name('store');
        Route::get('/{advertisement}', [AdvertisementController::class, 'show'])->name('show');
        Route::get('/{advertisement}/edit', [AdvertisementController::class, 'edit'])->name('edit');
        Route::put('/{advertisement}', [AdvertisementController::class, 'update'])->name('update');
        Route::delete('/{advertisement}', [AdvertisementController::class, 'destroy'])->name('destroy');
        Route::patch('/{advertisement}/toggle-status', [AdvertisementController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{advertisement}/click', [AdvertisementController::class, 'trackClick'])->name('click');
        Route::get('/display', [AdvertisementController::class, 'getForDisplay'])->name('display');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('can:view-users');
        Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('can:create-users');
        Route::post('/', [UserController::class, 'store'])->name('store')->middleware('can:create-users');
        Route::get('/{user}', [UserController::class, 'show'])->name('show')->middleware('can:view-users');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit')->middleware('can:edit-users');
        Route::put('/{user}', [UserController::class, 'update'])->name('update')->middleware('can:edit-users');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy')->middleware('can:delete-users');

        // Doctor ↔ Assistant assignments (controller enforces admin or self-doctor)
        Route::post('/{user}/assistants', [UserController::class, 'attachAssistant'])->name('assistants.attach');
        Route::delete('/{user}/assistants/{assistant}', [UserController::class, 'detachAssistant'])->name('assistants.detach');

        // Activation codes (permission-gated)
        Route::get('/activation-codes', [UserController::class, 'activationCodes'])->name('activation-codes')->middleware('can:manage-activation-codes');
        Route::post('/activation-codes', [UserController::class, 'generateActivationCode'])->name('activation-codes.generate')->middleware('can:manage-activation-codes');
        Route::delete('/activation-codes/{code}', [UserController::class, 'deleteActivationCode'])->name('activation-codes.delete')->middleware('can:manage-activation-codes');
        Route::patch('/activation-codes/{code}/extend', [UserController::class, 'extendActivationCode'])->name('activation-codes.extend')->middleware('can:manage-activation-codes');
    });

    // WhatsApp Management (Settings section permissions)
    Route::prefix('whatsapp')->name('whatsapp.')->middleware('can:access-settings')->group(function () {
        Route::get('/', [App\Http\Controllers\WhatsAppController::class, 'index'])->name('index');
        Route::post('/test', [App\Http\Controllers\WhatsAppController::class, 'test'])->name('test');
        Route::post('/setup', [App\Http\Controllers\WhatsAppController::class, 'setupWhatsAppWeb'])->name('setup');
        Route::get('/setup-status', [App\Http\Controllers\WhatsAppController::class, 'checkSetupStatus'])->name('setup-status');
        Route::get('/qr', [App\Http\Controllers\WhatsAppController::class, 'qrCode'])->name('qr');
        Route::get('/patients', [App\Http\Controllers\WhatsAppController::class, 'patientsList'])->name('patients');
        Route::post('/broadcast', [App\Http\Controllers\WhatsAppController::class, 'broadcast'])->name('broadcast');
        Route::post('/configure/twilio', [App\Http\Controllers\WhatsAppController::class, 'configureTwilio'])->name('configure.twilio');
        // Meta configuration moved to Master Admin panel — see routes/master.php

        // WPPConnect (free self-hosted WhatsApp)
        Route::post('/configure/wppconnect', [App\Http\Controllers\WhatsAppController::class, 'configureWppconnect'])->name('configure.wppconnect');
        Route::get('/wppconnect/qr', [App\Http\Controllers\WhatsAppController::class, 'wppconnectQr'])->name('wppconnect.qr');
        Route::get('/wppconnect/status', [App\Http\Controllers\WhatsAppController::class, 'wppconnectStatus'])->name('wppconnect.status');
    });

    // Notification Settings (WhatsApp auto-reminders)
    Route::prefix('notifications')->middleware('module:whatsapp')->group(function () {
        // Settings page — clinic admins only
        Route::get('/settings', [App\Http\Controllers\NotificationSettingsController::class, 'index'])->name('notifications.settings')->middleware('role:admin,super_admin');
        Route::post('/settings', [App\Http\Controllers\NotificationSettingsController::class, 'update'])->name('notifications.settings.update')->middleware('role:admin,super_admin');
        // Manual reminder trigger — any authenticated clinic user
        Route::post('/send-reminder', [App\Http\Controllers\NotificationSettingsController::class, 'sendReminder'])->name('notifications.send-reminder');
    });

	    // Internal Messaging & Transfers
	    Route::prefix('messages')->name('messages.')->middleware('module:messages')->group(function () {
	        // UI Page
	        Route::get('/', [\App\Http\Controllers\MessagesPageController::class, 'index'])->name('index');

	        // API Endpoints
	        Route::get('/conversations', [MessagingController::class, 'conversations'])->name('conversations');
	        Route::post('/conversations', [MessagingController::class, 'createConversation'])->name('conversations.create');
	        Route::get('/conversations/{conversation}/messages', [MessagingController::class, 'conversationMessages'])->name('conversations.messages');
	        Route::post('/send', [MessagingController::class, 'sendMessage'])->name('send');
	        Route::post('/{conversation}/read', [MessagingController::class, 'markRead'])->name('read');
	        Route::get('/unread-count', [MessagingController::class, 'unreadCount'])->name('unread-count');
	        Route::post('/transfers/{transfer}/action', [MessagingController::class, 'transferAction'])->name('transfers.action');
                Route::get('/recipients', [MessagingController::class, 'recipients'])->name('recipients');

                Route::post('/conversations/{conversation}/archive', [MessagingController::class, 'archiveConversation'])->name('conversations.archive');

                Route::post('/conversations/{conversation}/delete', [MessagingController::class, 'deleteConversation'])->name('conversations.delete');

	    });


    // Settings (admin-only via gate)
    Route::prefix('settings')->name('settings.')->middleware('can:access-settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');

        Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('update-profile');
        Route::post('/clinic-info', [SettingsController::class, 'updateClinicInfo'])->name('update-clinic-info');
        Route::delete('/logo', [SettingsController::class, 'deleteLogo'])->name('delete-logo');

        // System maintenance (permission-gated via Settings section)
        Route::post('/backup', [SettingsController::class, 'backup'])->name('backup')->middleware(['can:access-settings', 'throttle:1,15']);
        Route::get('/backup/download/{file}', [SettingsController::class, 'downloadBackup'])
            ->where('file', '.*')
            ->name('download-backup')
            ->middleware('can:access-settings');

        Route::post('/backup/direct', [SettingsController::class, 'backupDirect'])
            ->name('backup-direct')
            ->middleware(['can:access-settings', 'throttle:1,15']);
        Route::post('/system/auto-backup/clinic', [SettingsController::class, 'setClinicAutoBackup'])->name('system.auto-backup-clinic')->middleware('can:access-settings');
        Route::post('/backup/types', [SettingsController::class, 'setManualBackupDocTypes'])->name('backup-types')->middleware('can:access-settings');
        Route::post('/backup/include-db', [SettingsController::class, 'setManualBackupIncludeDb'])->name('backup-include-db')->middleware('can:access-settings');

        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache')->middleware('can:access-settings');
        Route::post('/update-system', [SettingsController::class, 'updateSystem'])->name('update-system')->middleware('can:access-settings');
        Route::post('/session-lifetime', [SettingsController::class, 'updateSessionLifetime'])->name('update-session-lifetime')->middleware('can:access-settings');
        Route::post('/patient-inactivity-period', [SettingsController::class, 'updatePatientInactivityPeriod'])->name('update-patient-inactivity-period')->middleware('can:access-settings');

        // Prescription Template management
        Route::post('/prescription-template/upload', [SettingsController::class, 'uploadPrescriptionTemplate'])->name('prescription-template.upload');
        Route::delete('/prescription-template', [SettingsController::class, 'deletePrescriptionTemplate'])->name('prescription-template.delete');
        Route::post('/prescription-template/settings', [SettingsController::class, 'savePrescriptionTemplateSettings'])->name('prescription-template.settings');

        // Generic Report Template management (blank_report, radiology, lab_request, diet_plan, dental, invoice)
        Route::post('/report-template/{type}/upload', [SettingsController::class, 'uploadReportTemplate'])->name('report-template.upload');
        Route::delete('/report-template/{type}', [SettingsController::class, 'deleteReportTemplate'])->name('report-template.delete');
        Route::post('/report-template/{type}/settings', [SettingsController::class, 'saveReportTemplateSettings'])->name('report-template.settings');

        // Audit logs (permission-gated)
        Route::get('/audit-logs', [SettingsController::class, 'auditLogs'])->name('audit-logs')->middleware('can:view-audit-logs');

        // User guide export
        Route::post('/export-user-guide', [SettingsController::class, 'exportUserGuide'])->name('export-user-guide');

        // User guide fullscreen view
        Route::get('/user-guide', [SettingsController::class, 'userGuide'])->name('user-guide');
    });


});



// Public route: Serve clinic logo without requiring auth (for PDFs and public embeds)
Route::get('/clinic-logo/{clinic}', [\App\Http\Controllers\SettingsController::class, 'serveClinicLogo'])->name('clinic.logo');

    // Forms Management (top-level, not nested under Recommendations)
    Route::prefix('forms')->name('forms.')->group(function () {
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [FormTemplateController::class, 'index'])->name('index');
            Route::get('/create', [FormTemplateController::class, 'create'])->name('create');
            Route::post('/', [FormTemplateController::class, 'store'])->name('store');
            Route::get('/{formTemplate}/edit', [FormTemplateController::class, 'edit'])->name('edit');
            Route::put('/{formTemplate}', [FormTemplateController::class, 'update'])->name('update');
            Route::delete('/{formTemplate}', [FormTemplateController::class, 'destroy'])->name('destroy');
            Route::get('/{formTemplate}/download', [FormTemplateController::class, 'download'])->name('download');
        });
    });

// Development routes (remove in production)
if (config('app.debug')) {

    // Debug dashboard access (bypass middleware)
    Route::get('/dev/dashboard', [DashboardController::class, 'index'])->name('dev.dashboard');

    // Development login shortcuts
    Route::get('/dev/login-admin', function () {
        $defaultClinic = \App\Models\Clinic::first();
        $hasClinicActivationCode = \Illuminate\Support\Facades\Schema::hasColumn('clinics', 'activation_code');
        $hasClinicEnabledModules = \Illuminate\Support\Facades\Schema::hasColumn('clinics', 'enabled_modules');

        $clinicCreateData = [
            'name' => 'Default Clinic',
            'email' => 'admin@defaultclinic.com',
            'phone' => '123456789',
            'address' => 'Default Address',
            'is_active' => true,
            'activated_at' => now(),
            'max_users' => 50,
        ];

        if ($hasClinicActivationCode) {
            $clinicCreateData['activation_code'] = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10));
        }

        if ($hasClinicEnabledModules) {
            $clinicCreateData['enabled_modules'] = array_keys(\App\Models\Clinic::AVAILABLE_MODULES);
        }

        $clinicUpdateData = [
            'is_active' => true,
            'activated_at' => $defaultClinic?->activated_at ?? now(),
        ];

        if ($hasClinicEnabledModules) {
            $clinicUpdateData['enabled_modules'] = array_keys(\App\Models\Clinic::AVAILABLE_MODULES);
        }

        if (!$defaultClinic) {
            $defaultClinic = \App\Models\Clinic::create($clinicCreateData);
        } else {
            $defaultClinic->update($clinicUpdateData);
        }

        $allPermissions = collect(\App\Models\User::getAllPermissions())
            ->flatMap(fn ($sectionPermissions) => array_keys($sectionPermissions))
            ->unique()
            ->values()
            ->all();

        // Find or create admin user
        $admin = \App\Models\User::where('username', 'admin')->first();

        if (!$admin) {
            // Create admin user if doesn't exist
            $admin = \App\Models\User::create([
                'username' => 'admin',
                'email' => 'admin@concure.local',
                'password' => bcrypt('admin123'),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'role' => 'admin',
                'clinic_id' => $defaultClinic->id,
                'permissions' => $allPermissions,
                'is_active' => true,
                'activated_at' => now(),
            ]);
        } else {
            $admin->update([
                'role' => 'admin',
                'clinic_id' => $defaultClinic->id,
                'permissions' => $allPermissions,
                'is_active' => true,
                'activated_at' => $admin->activated_at ?? now(),
            ]);
        }

        // Log in the user
        Auth::login($admin);

        return redirect('/dashboard')->with('success', 'Logged in as Admin (Development Mode)');
    })->name('dev.login-admin');

    Route::get('/dev/login-doctor', function () {
        // Find or create doctor user
        $doctor = \App\Models\User::where('username', 'doctor')->first();

        if (!$doctor) {
            // Create doctor user if doesn't exist
            $doctor = \App\Models\User::create([
                'username' => 'doctor',
                'email' => 'doctor@concure.local',
                'password' => bcrypt('doctor123'),
                'first_name' => 'Dr. Demo',
                'last_name' => 'Doctor',
                'role' => 'doctor',
                'is_active' => true,
                'activated_at' => now(),
            ]);
        }

        // Log in the user
        Auth::login($doctor);

        return redirect('/dashboard')->with('success', 'Logged in as Doctor (Development Mode)');
    })->name('dev.login-doctor');

    // Test Word export without middleware
    Route::get('/test-word-export/{dietPlan}', function(\App\Models\DietPlan $dietPlan) {
        try {
            $wordService = new \App\Services\WordDocumentService();
            $nutritionalTotals = ['calories' => 2000, 'protein' => 100, 'carbs' => 250, 'fat' => 70];
            $htmlContent = $wordService->generateNutritionPlan($dietPlan, $nutritionalTotals);

            $filename = "test-nutrition-plan-{$dietPlan->plan_number}.doc";

            return response($htmlContent, 200, [
                'Content-Type' => 'application/msword',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    })->name('test.word.export');

}

// Simple connectivity test
Route::get('/test-connection', function() {
    return response()->json([
        'status' => 'connected',
        'timestamp' => now()->toDateTimeString(),
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
        'database_connection' => 'testing...'
    ]);
});

// Diagnostic routes (always available for debugging)
Route::get('/debug-word-export/{id}', function($id) {
    try {
        // Check if diet plan exists
        $dietPlan = \App\Models\DietPlan::find($id);
        if (!$dietPlan) {
            return response()->json([
                'error' => 'DietPlan not found',
                'id' => $id,
                'available_ids' => \App\Models\DietPlan::pluck('id')->take(10)->toArray()
            ], 404);
        }

        // Load relationships
        $dietPlan->load(['patient', 'doctor', 'meals.foods.food']);

        // Test Word service
        $wordService = new \App\Services\WordDocumentService();
        $nutritionalTotals = ['calories' => 2000, 'protein' => 100, 'carbs' => 250, 'fat' => 70];
        $htmlContent = $wordService->generateNutritionPlan($dietPlan, $nutritionalTotals);

        return response()->json([
            'success' => true,
            'diet_plan_id' => $dietPlan->id,
            'plan_number' => $dietPlan->plan_number,
            'patient_name' => $dietPlan->patient->name ?? 'Unknown',
            'meals_count' => $dietPlan->meals->count(),
            'content_length' => strlen($htmlContent),
            'content_preview' => substr($htmlContent, 0, 200) . '...'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ], 500);
    }
})->name('debug.word.export');

// Check available diet plans
Route::get('/debug-diet-plans', function() {
    try {
        $plans = \App\Models\DietPlan::with(['patient', 'meals'])
            ->take(20)
            ->get(['id', 'plan_number', 'patient_id', 'created_at'])
            ->map(function($plan) {
                return [
                    'id' => $plan->id,
                    'plan_number' => $plan->plan_number,
                    'patient_name' => $plan->patient->name ?? 'Unknown',
                    'meals_count' => $plan->meals->count(),
                    'created_at' => $plan->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'total_plans' => \App\Models\DietPlan::count(),
            'sample_plans' => $plans,
            'app_debug' => config('app.debug'),
            'app_env' => config('app.env')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('debug.diet.plans');

// Debug: fetch plan by plan_number quickly
Route::get('/debug-plan-by-number/{plan_number}', function($plan_number) {
    try {
        $plan = \App\Models\DietPlan::where('plan_number', $plan_number)
            ->with(['patient', 'meals.foods'])
            ->first();
        if (!$plan) {
            return response()->json([
                'error' => 'DietPlan not found',
                'plan_number' => $plan_number,
            ], 404);
        }

        $siblings = \App\Models\DietPlan::where('patient_id', $plan->patient_id)
            ->orderBy('created_at', 'desc')
            ->withCount('meals')
            ->take(10)
            ->get(['id','plan_number','patient_id','created_at']);

        return response()->json([
            'success' => true,
            'plan' => [
                'id' => $plan->id,
                'plan_number' => $plan->plan_number,
                'patient_id' => $plan->patient_id,
                'meals_count' => $plan->meals->count(),
                'foods_total' => $plan->meals->sum(function($m){ return $m->foods->count(); }),
                'created_at' => (string) $plan->created_at,
            ],
            'recent_for_same_patient' => $siblings->map(function($p){
                return [
                    'id' => $p->id,
                    'plan_number' => $p->plan_number,
                    'meals_count' => $p->meals_count,
                    'created_at' => (string) $p->created_at,
                ];
            }),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null,
        ], 500);
    }
})->middleware(['auth'])->name('debug.plan.by.number');

// Debug/repair: copy meals from one plan to another by plan_number (dry-run by default)
Route::get('/debug-copy-meals/{from}/{to}', function($from, $to) {
    $dryRun = filter_var(request()->query('dry_run', '1'), FILTER_VALIDATE_BOOLEAN);
    try {
        $src = \App\Models\DietPlan::where('plan_number', $from)
            ->with(['patient', 'meals.foods'])
            ->first();
        $dst = \App\Models\DietPlan::where('plan_number', $to)
            ->with(['patient', 'meals'])
            ->first();

        if (!$src || !$dst) {
            return response()->json([
                'error' => 'Source or destination plan not found',
                'from' => $from,
                'to' => $to,
            ], 404);
        }
        if ($src->patient_id !== $dst->patient_id) {
            return response()->json([
                'error' => 'Source and destination must belong to the same patient',
                'src_patient_id' => $src->patient_id,
                'dst_patient_id' => $dst->patient_id,
            ], 422);
        }

        $summary = [
            'from' => [
                'id' => $src->id,
                'plan_number' => $src->plan_number,
                'meals_count' => $src->meals->count(),
                'foods_total' => $src->meals->sum(function($m){ return $m->foods->count(); }),
            ],
            'to' => [
                'id' => $dst->id,
                'plan_number' => $dst->plan_number,
                'meals_count_before' => $dst->meals->count(),
            ],
            'dry_run' => $dryRun,
        ];

        if ($dryRun) {
            $summary['action'] = 'No changes made. Call with ?dry_run=0 to perform copy.';
            return response()->json($summary);
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($src, $dst, &$summary) {
            // Remove existing meals on destination (if any)
            $deleted = $dst->meals()->delete();
            $copiedMeals = 0; $copiedFoods = 0;

            foreach ($src->meals as $meal) {
                $newMeal = $dst->meals()->create([
                    'day_number' => $meal->day_number,
                    'meal_type' => $meal->meal_type,
                    'option_number' => $meal->option_number,
                    'is_option_based' => (bool) $meal->is_option_based,
                    'option_description' => $meal->option_description,
                    'meal_name' => $meal->meal_name,
                    'instructions' => $meal->instructions,
                    'suggested_time' => $meal->suggested_time,
                ]);
                $copiedMeals++;

                foreach ($meal->foods as $food) {
                    $newMeal->foods()->create([
                        'food_id' => $food->food_id,
                        'food_name' => $food->food_name,
                        'quantity' => $food->quantity,
                        'unit' => $food->unit,
                        'preparation_notes' => $food->preparation_notes,
                    ]);
                    $copiedFoods++;
                }
            }

            $summary['to']['meals_count_after'] = $dst->meals()->count();
            $summary['copied'] = [
                'meals' => $copiedMeals,
                'foods' => $copiedFoods,
                'deleted_before_copy' => $deleted,
            ];
        });

        $summary['status'] = 'copied';
        return response()->json($summary);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null,
        ], 500);
    }
})->middleware(['auth'])->name('debug.copy.meals');


if (config('app.debug')) {
    // Fix dashboard access issues
    Route::get('/dev/fix-dashboard', function () {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login')->with('error', 'Please log in first');
        }

        // Fix user activation
        $user->update([
            'activated_at' => now(),
            'is_active' => true
        ]);

        // Fix clinic issues
        if ($user->clinic) {
            $user->clinic->update([
                'is_active' => true,
                'activated_at' => now(),

            ]);
        } else {
            // Create or assign default clinic
            $defaultClinic = \App\Models\Clinic::first();
            if (!$defaultClinic) {
                $defaultClinic = \App\Models\Clinic::create([
                    'name' => 'Default Clinic',
                    'email' => 'admin@defaultclinic.com',
                    'phone' => '123456789',
                    'address' => 'Default Address',
                    'is_active' => true,
                    'activated_at' => now(),

                    'max_users' => 50,
                ]);
            }
            $user->update(['clinic_id' => $defaultClinic->id]);
        }

        return redirect('/dashboard')->with('success', 'Dashboard access issues fixed! You should now be able to access the dashboard.');
    });

    Route::get('/dev/make-admin', function () {
        $user = auth()->user();
        if ($user) {
            $defaultClinic = $user->clinic ?: \App\Models\Clinic::first();

            if (!$defaultClinic) {
                $defaultClinic = \App\Models\Clinic::create([
                    'name' => 'Default Clinic',
                    'email' => 'admin@defaultclinic.com',
                    'phone' => '123456789',
                    'address' => 'Default Address',
                    'is_active' => true,
                    'activated_at' => now(),
                    'activation_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
                    'max_users' => 50,
                    'enabled_modules' => array_keys(\App\Models\Clinic::AVAILABLE_MODULES),
                ]);
            } else {
                $defaultClinic->update([
                    'is_active' => true,
                    'activated_at' => $defaultClinic->activated_at ?? now(),
                    'enabled_modules' => array_keys(\App\Models\Clinic::AVAILABLE_MODULES),
                ]);
            }

            $allPermissions = collect(\App\Models\User::getAllPermissions())
                ->flatMap(fn ($sectionPermissions) => array_keys($sectionPermissions))
                ->unique()
                ->values()
                ->all();

            $user->update([
                'role' => 'admin',
                'clinic_id' => $defaultClinic->id,
                'permissions' => $allPermissions,
                'is_active' => true,
                'activated_at' => $user->activated_at ?? now(),
            ]);
            return "✅ Successfully updated {$user->first_name} {$user->last_name} to Admin role! Please refresh your browser.";
        }
        return "❌ No user logged in.";
    })->middleware('auth');
}

// Test route for debugging AJAX issues (temporary)
Route::get('/test-lab-request/{id}', function($id) {
    try {
        $labRequest = App\Models\LabRequest::with(['patient', 'doctor', 'tests'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'labRequest' => [
                'id' => $labRequest->id,
                'request_number' => $labRequest->request_number,
                'status' => $labRequest->status,
                'patient' => [
                    'full_name' => $labRequest->patient->full_name,
                    'phone' => $labRequest->patient->phone,
                ],
                'tests' => $labRequest->tests->pluck('test_name'),
            ],
            'message' => 'Test endpoint working!'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Clean URL redirect route
Route::get('/lab-requests', function() {
    return redirect('/recommendations/lab-requests');
});

// Dental Module Test Route
Route::get('/test-dental', function() {
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'error' => 'Not authenticated. Please login first.',
            'login_url' => route('login')
        ]);
    }

    // Test database tables exist
    $tables = [
        'dental_charts' => \Illuminate\Support\Facades\Schema::hasTable('dental_charts'),
        'dental_tooth_records' => \Illuminate\Support\Facades\Schema::hasTable('dental_tooth_records'),
        'dental_treatments' => \Illuminate\Support\Facades\Schema::hasTable('dental_treatments'),
        'dental_images' => \Illuminate\Support\Facades\Schema::hasTable('dental_images'),
        'dental_procedures' => \Illuminate\Support\Facades\Schema::hasTable('dental_procedures'),
    ];

    // Test models can be instantiated
    $models = [
        'DentalChart' => class_exists('App\Models\DentalChart'),
        'DentalToothRecord' => class_exists('App\Models\DentalToothRecord'),
        'DentalTreatment' => class_exists('App\Models\DentalTreatment'),
        'DentalImage' => class_exists('App\Models\DentalImage'),
        'DentalProcedure' => class_exists('App\Models\DentalProcedure'),
    ];

    // Get counts
    $counts = [
        'dental_charts' => \App\Models\DentalChart::count(),
        'dental_tooth_records' => \App\Models\DentalToothRecord::count(),
        'dental_treatments' => \App\Models\DentalTreatment::count(),
        'dental_images' => \App\Models\DentalImage::count(),
        'dental_procedures' => \App\Models\DentalProcedure::count(),
    ];

    // Get a sample patient
    $patient = \App\Models\Patient::when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                                  ->first();

    return response()->json([
        'success' => true,
        'message' => 'Dental Module Test',
        'user' => [
            'name' => $user->name,
            'role' => $user->role,
            'clinic_id' => $user->clinic_id,
        ],
        'tables_exist' => $tables,
        'models_loaded' => $models,
        'record_counts' => $counts,
        'sample_patient' => $patient ? [
            'id' => $patient->id,
            'name' => $patient->full_name,
            'patient_id' => $patient->patient_id,
        ] : null,
        'routes' => [
            'dental_charts_index' => $patient ? url("/dental/patients/{$patient->id}/charts") : 'Need patient',
            'dental_treatments_index' => url('/dental/treatments'),
            'test_create_chart' => $patient ? url("/dental/patients/{$patient->id}/charts/create") : 'Need patient',
        ],
    ]);
})->middleware('auth')->name('test-dental');

// Create Sample Dental Chart
Route::get('/test-dental/create-sample', function() {
    $user = auth()->user();

    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }

    // Get first patient
    $patient = \App\Models\Patient::when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                                  ->first();

    if (!$patient) {
        return response()->json(['error' => 'No patient found. Please create a patient first.']);
    }

    // Create a dental chart
    $chart = \App\Models\DentalChart::create([
        'patient_id' => $patient->id,
        'clinic_id' => $user->clinic_id ?? $patient->clinic_id ?? 1,
        'chart_type' => 'adult',
        'general_notes' => 'Sample dental chart created for testing',
        'created_by' => $user->id,
    ]);

    // Add some sample tooth records
    $sampleTeeth = [
        ['tooth_number' => '11', 'primary_condition' => 'healthy', 'conditions' => ['healthy']],
        ['tooth_number' => '12', 'primary_condition' => 'healthy', 'conditions' => ['healthy']],
        ['tooth_number' => '16', 'primary_condition' => 'caries', 'conditions' => ['caries'], 'surfaces_affected' => ['O', 'M'], 'severity' => 'moderate', 'notes' => 'Cavity on occlusal and mesial surfaces'],
        ['tooth_number' => '21', 'primary_condition' => 'filling', 'conditions' => ['filling'], 'surfaces_affected' => ['O'], 'notes' => 'Composite filling in good condition'],
        ['tooth_number' => '26', 'primary_condition' => 'crown', 'conditions' => ['crown'], 'notes' => 'Porcelain crown'],
        ['tooth_number' => '36', 'primary_condition' => 'root_canal', 'conditions' => ['root_canal', 'crown'], 'notes' => 'Root canal with crown'],
        ['tooth_number' => '46', 'primary_condition' => 'caries', 'conditions' => ['caries'], 'surfaces_affected' => ['D', 'O'], 'severity' => 'mild', 'notes' => 'Small cavity'],
    ];

    foreach ($sampleTeeth as $toothData) {
        \App\Models\DentalToothRecord::create(array_merge($toothData, [
            'dental_chart_id' => $chart->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]));
    }

    // Create a sample treatment plan
    $treatment = \App\Models\DentalTreatment::create([
        'patient_id' => $patient->id,
        'clinic_id' => $user->clinic_id ?? $patient->clinic_id ?? 1,
        'dental_chart_id' => $chart->id,
        'tooth_number' => '16',
        'procedure_name' => 'Composite Filling - Two Surfaces',
        'procedure_code' => 'D2331',
        'diagnosis' => 'Dental caries on tooth #16',
        'surfaces_affected' => ['O', 'M'],
        'description' => 'Remove caries and place composite filling on occlusal and mesial surfaces',
        'estimated_cost' => 210.00,
        'currency' => 'USD',
        'estimated_duration_minutes' => 45,
        'status' => 'planned',
        'priority' => 'medium',
        'severity' => 'moderate',
        'assigned_doctor_id' => $user->id,
        'payment_status' => 'unpaid',
        'paid_amount' => 0,
        'notes' => 'Patient prefers tooth-colored filling',
        'created_by' => $user->id,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Sample dental chart created successfully!',
        'chart' => [
            'id' => $chart->id,
            'patient' => $patient->full_name,
            'chart_type' => $chart->chart_type,
            'tooth_records_count' => $chart->toothRecords()->count(),
        ],
        'treatment' => [
            'id' => $treatment->id,
            'treatment_number' => $treatment->treatment_number,
            'procedure' => $treatment->procedure_name,
            'status' => $treatment->status,
            'cost' => '$' . number_format($treatment->estimated_cost, 2),
        ],
        'view_urls' => [
            'chart' => url("/dental/patients/{$patient->id}/charts/{$chart->id}"),
            'treatment' => url("/dental/treatments/{$treatment->id}"),
            'all_treatments' => url("/dental/treatments"),
        ],
    ]);
})->middleware('auth')->name('test-dental.create-sample');
