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


use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

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

// CSRF Token Refresh Route (for preventing 419 errors)
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf-token');

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
// Backward compatibility redirects
Route::get('/auth/login', function () { return redirect()->route('login'); });
Route::post('/auth/login', function () { return redirect('/login'); });
Route::post('/auth/logout', function () { return redirect('/'); });

// Public clinic activation routes
Route::get('/activate-clinic', [ClinicActivationController::class, 'showActivationForm'])->name('clinic.activate.form');
Route::post('/activate-clinic', [ClinicActivationController::class, 'activate'])->name('clinic.activate');
Route::post('/validate-activation-code', [ClinicActivationController::class, 'validateCode'])->name('clinic.validate-code');

// Public invoice access (for patients via email links)
Route::get('/invoice/{invoice}/pdf/{token}', [FinanceController::class, 'publicInvoicePDF'])->name('finance.invoices.public.pdf');
Route::get('/invoice/{invoice}/view/{token}', [FinanceController::class, 'publicInvoiceView'])->name('finance.invoices.public.view');

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

// Subscription system removed - no longer needed

// Protected routes
Route::middleware(['auth', 'activation'])->group(function () {
    
    // Tenant Dashboard (Clinic Users Only)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Patient Management
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/create', [PatientController::class, 'create'])->name('create');
        Route::post('/', [PatientController::class, 'store'])->name('store');

        // Import routes (must be before parameterized routes)
        Route::get('/import', [PatientController::class, 'showImport'])->name('import');
        Route::post('/import', [PatientController::class, 'import'])->name('import.process');
        Route::get('/import/template', [PatientController::class, 'downloadTemplate'])->name('import.template');

        // API route for dropdowns (must be before parameterized routes)
        Route::get('/api', [PatientController::class, 'apiList'])->name('api');

        Route::get('/{patient}', [PatientController::class, 'show'])->name('show');
        Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::put('/{patient}', [PatientController::class, 'update'])->name('update');
        Route::delete('/{patient}', [PatientController::class, 'destroy'])->name('destroy');

        // Patient specific routes
        Route::get('/{patient}/history', [PatientController::class, 'history'])->name('history');
        Route::post('/{patient}/checkup', [PatientController::class, 'addCheckup'])->name('checkup');
        Route::post('/{patient}/upload', [PatientController::class, 'uploadFile'])->name('upload');
    });

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

    // Custom Vital Signs Management (Admin only)
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        Route::resource('custom-vital-signs', App\Http\Controllers\CustomVitalSignsController::class);
        Route::patch('custom-vital-signs/{customVitalSign}/toggle-status', [App\Http\Controllers\CustomVitalSignsController::class, 'toggleStatus'])->name('custom-vital-signs.toggle-status');

        // Custom Checkup Templates Management
        Route::resource('checkup-templates', App\Http\Controllers\CustomCheckupTemplateController::class);
        Route::patch('checkup-templates/{template}/toggle-status', [App\Http\Controllers\CustomCheckupTemplateController::class, 'toggleStatus'])->name('checkup-templates.toggle-status');
        Route::post('checkup-templates/{template}/clone', [App\Http\Controllers\CustomCheckupTemplateController::class, 'clone'])->name('checkup-templates.clone');
        Route::get('checkup-templates/{template}/preview', [App\Http\Controllers\CustomCheckupTemplateController::class, 'preview'])->name('checkup-templates.preview');
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
        Route::post('/', [SimplePrescriptionController::class, 'store'])->name('store');
        Route::get('/{prescription}', [SimplePrescriptionController::class, 'show'])->name('show');
        Route::get('/{prescription}/edit', [SimplePrescriptionController::class, 'edit'])->name('edit');
        Route::put('/{prescription}', [SimplePrescriptionController::class, 'update'])->name('update');
        Route::delete('/{prescription}', [SimplePrescriptionController::class, 'destroy'])->name('destroy');
        Route::get('/{prescription}/pdf', [SimplePrescriptionController::class, 'pdf'])->name('pdf');
        Route::get('/{prescription}/print', [SimplePrescriptionController::class, 'print'])->name('print');
    });

    // Medicine Management
    Route::prefix('medicines')->name('medicines.')->group(function () {
        Route::get('/', [App\Http\Controllers\MedicineController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\MedicineController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\MedicineController::class, 'store'])->name('store');
        Route::get('/search', [App\Http\Controllers\MedicineController::class, 'search'])->name('search');

        // Import routes
        Route::get('/import', [App\Http\Controllers\MedicineController::class, 'showImport'])->name('import');
        Route::post('/import', [App\Http\Controllers\MedicineController::class, 'import'])->name('import.process');
        Route::get('/import/template', [App\Http\Controllers\MedicineController::class, 'downloadTemplate'])->name('import.template');

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
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::get('/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
        Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy');
        Route::patch('/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('update-status');
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



        // Temporary login switcher for testing
        Route::get('/login-as/{userId}', function($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                auth()->login($user);
                $canCreateLabRequests = $user->hasPermission('prescriptions_create') ? 'YES' : 'NO';
                return redirect('/dashboard')->with('success',
                    "Logged in as {$user->first_name} {$user->last_name} ({$user->role}). " .
                    "Can create lab requests: {$canCreateLabRequests}"
                );
            }
            return redirect('/')->with('error', 'User not found');
        });

        // Quick login links - Fixed to find actual users
        Route::get('/login-as-doctor', function() {
            $doctor = \App\Models\User::where('role', 'doctor')->where('is_active', true)->first();
            if ($doctor) {
                return redirect('/recommendations/login-as/' . $doctor->id);
            }
            return redirect('/')->with('error', 'No active doctor found');
        });

        Route::get('/login-as-admin', function() {
            $admin = \App\Models\User::where('role', 'admin')->where('is_active', true)->first();
            if ($admin) {
                return redirect('/recommendations/login-as/' . $admin->id);
            }
            return redirect('/')->with('error', 'No active admin found');
        });

        // Direct demo login routes (easier to use)
        Route::get('/dev/login-admin', function() {
            $admin = \App\Models\User::where('role', 'admin')->where('is_active', true)->first();
            if (!$admin) {
                // Create admin if doesn't exist
                $clinic = \App\Models\Clinic::first();
                if (!$clinic) {
                    $clinic = \App\Models\Clinic::create([
                        'name' => 'Demo Clinic',
                        'email' => 'demo@clinic.com',
                        'phone' => '123456789',
                        'address' => 'Demo Address',
                        'is_active' => true,
                        'activation_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
                        'activated_at' => now(),
                        'max_users' => 50,
                    ]);
                }

                $admin = \App\Models\User::create([
                    'username' => 'admin',
                    'email' => 'admin@demo.clinic',
                    'password' => bcrypt('admin123'),
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'role' => 'admin',
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                    'permissions' => [
                        'dashboard_view', 'dashboard_stats',
                        'patients_view', 'patients_create', 'patients_edit', 'patients_delete',
                        'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_delete',
                        'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete',
                        'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete',
                        'users_view', 'users_create', 'users_edit', 'users_delete',
                        'settings_view', 'settings_edit',
                        'finance_view', 'finance_create', 'finance_edit', 'finance_delete',
                        'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete',
                        'radiology_view', 'radiology_create', 'radiology_edit', 'radiology_delete',
                        'lab_requests_view', 'lab_requests_create', 'lab_requests_edit', 'lab_requests_delete'
                    ]
                ]);
            }

            auth()->login($admin);
            return redirect('/dashboard')->with('success', 'Logged in as Demo Admin');
        });

        Route::get('/dev/login-doctor', function() {
            $doctor = \App\Models\User::where('role', 'doctor')->where('is_active', true)->first();
            if (!$doctor) {
                // Create doctor if doesn't exist
                $clinic = \App\Models\Clinic::first();
                if (!$clinic) {
                    $clinic = \App\Models\Clinic::create([
                        'name' => 'Demo Clinic',
                        'email' => 'demo@clinic.com',
                        'phone' => '123456789',
                        'address' => 'Demo Address',
                        'is_active' => true,
                        'activation_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
                        'activated_at' => now(),
                        'max_users' => 50,
                    ]);
                }

                $doctor = \App\Models\User::create([
                    'username' => 'doctor',
                    'email' => 'doctor@demo.clinic',
                    'password' => bcrypt('doctor123'),
                    'first_name' => 'Dr. John',
                    'last_name' => 'Smith',
                    'role' => 'doctor',
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                    'permissions' => [
                        'dashboard_view', 'dashboard_stats',
                        'patients_view', 'patients_create', 'patients_edit',
                        'prescriptions_view', 'prescriptions_create', 'prescriptions_edit',
                        'appointments_view', 'appointments_create', 'appointments_edit',
                        'medicines_view',
                        'nutrition_view', 'nutrition_create', 'nutrition_edit',
                        'lab_requests_view', 'lab_requests_create', 'lab_requests_edit',
                        'ai_advisory_view', 'ai_advisory_use'
                    ]
                ]);
            }

            auth()->login($doctor);
            return redirect('/dashboard')->with('success', 'Logged in as Demo Doctor');
        });

        // Quick permission granting for testing
        Route::get('/grant-lab-permissions/{userId}', function($userId) {
            $user = auth()->user();

            // Only admins can grant permissions
            if ($user->role !== 'admin') {
                abort(403, 'Only admins can grant permissions.');
            }

            $targetUser = \App\Models\User::find($userId);
            if (!$targetUser) {
                return redirect()->back()->with('error', 'User not found.');
            }

            $permissions = $targetUser->permissions ?? [];
            $requiredPermissions = ['prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_print'];

            foreach ($requiredPermissions as $permission) {
                if (!in_array($permission, $permissions)) {
                    $permissions[] = $permission;
                }
            }

            $targetUser->permissions = array_unique($permissions);
            $targetUser->save();

            return redirect()->back()->with('success', "Granted lab request permissions to {$targetUser->full_name}");
        })->name('grant-lab-permissions');
        
        // Prescriptions
        Route::get('/prescriptions', [RecommendationController::class, 'prescriptions'])->name('prescriptions');
        Route::post('/prescriptions', [RecommendationController::class, 'storePrescription'])->name('prescriptions.store');
        
        // Diet Plans
        Route::get('/diet-plans', [RecommendationController::class, 'dietPlans'])->name('diet-plans');
        Route::post('/diet-plans', [RecommendationController::class, 'storeDietPlan'])->name('diet-plans.store');
        Route::get('/diet-plans/{dietPlan}/pdf', [RecommendationController::class, 'generateDietPlanPDF'])->name('diet-plans.pdf');
    });
    
    // Food Composition
    Route::prefix('foods')->name('foods.')->group(function () {
        Route::get('/', [FoodController::class, 'index'])->name('index');
        Route::get('/create', [FoodController::class, 'create'])->name('create');
        Route::post('/', [FoodController::class, 'store'])->name('store');

        // Import routes must be before parameterized routes
        Route::get('/import', [FoodController::class, 'showImport'])->name('import');
        Route::post('/import', [FoodController::class, 'import'])->name('import.process');
        Route::get('/import/template', [FoodController::class, 'downloadTemplate'])->name('import.template');

        // Search route must be before parameterized routes
        Route::get('/search', [FoodController::class, 'search'])->name('search');

        // Clear all foods route must be before parameterized routes
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
    Route::prefix('finance')->name('finance.')->middleware('role:admin,accountant')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        
        // Invoices
        Route::get('/invoices', [FinanceController::class, 'invoices'])->name('invoices');
        Route::post('/invoices', [FinanceController::class, 'storeInvoice'])->name('invoices.store');
        Route::get('/invoices/{invoice}/edit', [FinanceController::class, 'getInvoiceForEdit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [FinanceController::class, 'updateInvoice'])->name('invoices.update');
        Route::get('/invoices/{invoice}/pdf', [FinanceController::class, 'generateInvoicePDF'])->name('invoices.pdf');
        Route::get('/invoices/{invoice}/print', [FinanceController::class, 'printInvoice'])->name('invoices.print');
        Route::get('/invoices/{invoice}/public-pdf-url', [FinanceController::class, 'getPublicPdfUrl'])->name('invoices.public-pdf-url');
        Route::get('/invoices/{invoice}/email-form', [FinanceController::class, 'showEmailForm'])->name('invoices.email-form');
        Route::post('/invoices/{invoice}/email', [FinanceController::class, 'emailInvoice'])->name('invoices.email');
        
        // Expenses
        Route::get('/expenses', [FinanceController::class, 'expenses'])->name('expenses');
        Route::post('/expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');
        Route::post('/expenses/{expense}/approve', [FinanceController::class, 'approveExpense'])->name('expenses.approve');
        Route::post('/expenses/{expense}/reject', [FinanceController::class, 'rejectExpense'])->name('expenses.reject');
        
        // Reports
        Route::get('/reports', [FinanceController::class, 'reports'])->name('reports');
        Route::get('/reports/cash-flow', [FinanceController::class, 'cashFlowReport'])->name('reports.cash-flow');
        Route::get('/reports/profit-loss', [FinanceController::class, 'profitLossReport'])->name('reports.profit-loss');
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

        // Activation codes (Admin only)
        Route::get('/activation-codes', [UserController::class, 'activationCodes'])->name('activation-codes')->middleware('role:admin');
        Route::post('/activation-codes', [UserController::class, 'generateActivationCode'])->name('activation-codes.generate')->middleware('role:admin');
        Route::delete('/activation-codes/{code}', [UserController::class, 'deleteActivationCode'])->name('activation-codes.delete')->middleware('role:admin');
        Route::patch('/activation-codes/{code}/extend', [UserController::class, 'extendActivationCode'])->name('activation-codes.extend')->middleware('role:admin');
    });

    // WhatsApp Management (Admin only)
    Route::prefix('whatsapp')->name('whatsapp.')->middleware('role:admin')->group(function () {
        Route::get('/', [App\Http\Controllers\WhatsAppController::class, 'index'])->name('index');
        Route::post('/test', [App\Http\Controllers\WhatsAppController::class, 'test'])->name('test');
        Route::post('/setup', [App\Http\Controllers\WhatsAppController::class, 'setupWhatsAppWeb'])->name('setup');
        Route::get('/setup-status', [App\Http\Controllers\WhatsAppController::class, 'checkSetupStatus'])->name('setup-status');
        Route::get('/qr', [App\Http\Controllers\WhatsAppController::class, 'qrCode'])->name('qr');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');

        Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('update-profile');
        Route::post('/clinic-info', [SettingsController::class, 'updateClinicInfo'])->name('update-clinic-info');
        Route::delete('/logo', [SettingsController::class, 'deleteLogo'])->name('delete-logo');

        // System maintenance (Admin only)
        Route::post('/backup', [SettingsController::class, 'backup'])->name('backup')->middleware('role:admin');
        Route::get('/backup/download/{file}', [SettingsController::class, 'downloadBackup'])->name('download-backup')->middleware('role:admin');
        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache')->middleware('role:admin');
        Route::post('/update-system', [SettingsController::class, 'updateSystem'])->name('update-system')->middleware('role:admin');

        // Audit logs (Admin only)
        Route::get('/audit-logs', [SettingsController::class, 'auditLogs'])->name('audit-logs')->middleware('role:admin');

        // User guide export
        Route::post('/export-user-guide', [SettingsController::class, 'exportUserGuide'])->name('export-user-guide');

        // User guide fullscreen view
        Route::get('/user-guide', [SettingsController::class, 'userGuide'])->name('user-guide');
    });
});

// Development routes (remove in production)
if (config('app.debug')) {

    // Debug dashboard access (bypass middleware)
    Route::get('/dev/dashboard', [DashboardController::class, 'index'])->name('dev.dashboard');

    // Create demo users if they don't exist
    Route::get('/dev/create-demo-users', function () {
        try {
            // Create or get default clinic
            $clinic = \App\Models\Clinic::first();
            if (!$clinic) {
                $clinic = \App\Models\Clinic::create([
                    'name' => 'Demo Clinic',
                    'email' => 'demo@clinic.com',
                    'phone' => '123456789',
                    'address' => 'Demo Address',
                    'is_active' => true,
                    'activated_at' => now(),

                    'max_users' => 50,
                ]);
            } else {
                $clinic->update([
                    'is_active' => true,
                    'activated_at' => now(),

                ]);
            }

            // Create or update admin user
            $adminUser = \App\Models\User::where('username', 'admin')->first();
            if (!$adminUser) {
                $adminUser = \App\Models\User::create([
                    'username' => 'admin',
                    'email' => 'admin@demo.clinic',
                    'password' => bcrypt('admin123'),
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'role' => 'admin',
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                    'permissions' => [
                        'dashboard_view', 'dashboard_stats',
                        'patients_view', 'patients_create', 'patients_edit', 'patients_delete',
                        'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_delete',
                        'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete',
                        'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete',
                        'users_view', 'users_create', 'users_edit', 'users_delete',
                        'settings_view', 'settings_edit',
                        'finance_view', 'finance_create', 'finance_edit', 'finance_delete',
                        'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete',
                        'radiology_view', 'radiology_create', 'radiology_edit', 'radiology_delete',
                        'lab_requests_view', 'lab_requests_create', 'lab_requests_edit', 'lab_requests_delete'
                    ]
                ]);
            } else {
                $adminUser->update([
                    'password' => bcrypt('admin123'),
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                ]);
            }

            // Create or update doctor user
            $doctorUser = \App\Models\User::where('username', 'doctor')->first();
            if (!$doctorUser) {
                $doctorUser = \App\Models\User::create([
                    'username' => 'doctor',
                    'email' => 'doctor@demo.clinic',
                    'password' => bcrypt('doctor123'),
                    'first_name' => 'Dr. John',
                    'last_name' => 'Smith',
                    'role' => 'doctor',
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                    'permissions' => [
                        'dashboard_view', 'dashboard_stats',
                        'patients_view', 'patients_create', 'patients_edit',
                        'prescriptions_view', 'prescriptions_create', 'prescriptions_edit',
                        'appointments_view', 'appointments_create', 'appointments_edit',
                        'medicines_view',
                        'nutrition_view', 'nutrition_create', 'nutrition_edit',
                        'lab_requests_view', 'lab_requests_create', 'lab_requests_edit'
                    ]
                ]);
            } else {
                $doctorUser->update([
                    'password' => bcrypt('doctor123'),
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Demo users created successfully!',
                'users' => [
                    'admin' => [
                        'username' => 'admin',
                        'password' => 'admin123',
                        'id' => $adminUser->id
                    ],
                    'doctor' => [
                        'username' => 'doctor',
                        'password' => 'doctor123',
                        'id' => $doctorUser->id
                    ]
                ],
                'clinic' => [
                    'name' => $clinic->name,
                    'id' => $clinic->id
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    });

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
            $user->update([
                'role' => 'admin',
                'permissions' => [
                    'dashboard_view', 'dashboard_stats',
                    'patients_view', 'patients_create', 'patients_edit', 'patients_delete', 'patients_files', 'patients_history',
                    'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_delete', 'prescriptions_print',
                    'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete', 'appointments_manage',
                    'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete', 'medicines_inventory',
                    'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete', 'nutrition_manage',
                    'radiology_view', 'radiology_create', 'radiology_edit', 'radiology_delete', 'radiology_manage',
                    'lab_requests_view', 'lab_requests_create', 'lab_requests_edit', 'lab_requests_delete',
                    'users_view', 'users_create', 'users_edit', 'users_delete', 'users_permissions',
                    'settings_view', 'settings_edit',
                    'reports_view', 'reports_generate', 'reports_export',
                    'finance_view', 'finance_create', 'finance_edit', 'finance_reports',
                    'audit_view', 'audit_export',
                ]
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

// Demo login routes (outside middleware groups for easy access)
Route::get('/dev/login-admin', function() {
    try {
        $admin = \App\Models\User::where('role', 'admin')->where('is_active', true)->first();
        if (!$admin) {
            // Create admin if doesn't exist
            $clinic = \App\Models\Clinic::first();
            if (!$clinic) {
                $clinic = \App\Models\Clinic::create([
                    'name' => 'Demo Clinic',
                    'email' => 'demo@clinic.com',
                    'phone' => '123456789',
                    'address' => 'Demo Address',
                    'is_active' => true,
                    'activation_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
                    'activated_at' => now(),
                    'max_users' => 50,
                ]);
            }

            $existing = \App\Models\User::where('username', 'admin')->first();
            if ($existing) {
                $existing->update([
                    'email' => 'admin@demo.clinic',
                    'password' => bcrypt('admin123'),
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'role' => 'admin',
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                    'permissions' => [
                        'dashboard_view', 'dashboard_stats',
                        'patients_view', 'patients_create', 'patients_edit', 'patients_delete',
                        'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_delete',
                        'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete',
                        'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete',
                        'users_view', 'users_create', 'users_edit', 'users_delete',
                        'settings_view', 'settings_edit',
                        'finance_view', 'finance_create', 'finance_edit', 'finance_delete',
                        'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete',
                        'radiology_view', 'radiology_create', 'radiology_edit', 'radiology_delete',
                        'lab_requests_view', 'lab_requests_create', 'lab_requests_edit', 'lab_requests_delete'
                    ]
                ]);
                $admin = $existing;
            } else {
                $admin = \App\Models\User::create([
                    'username' => 'admin',
                    'email' => 'admin@demo.clinic',
                    'password' => bcrypt('admin123'),
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'role' => 'admin',
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                    'permissions' => [
                        'dashboard_view', 'dashboard_stats',
                        'patients_view', 'patients_create', 'patients_edit', 'patients_delete',
                        'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_delete',
                        'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete',
                        'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete',
                        'users_view', 'users_create', 'users_edit', 'users_delete',
                        'settings_view', 'settings_edit',
                        'finance_view', 'finance_create', 'finance_edit', 'finance_delete',
                        'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete',
                        'radiology_view', 'radiology_create', 'radiology_edit', 'radiology_delete',
                        'lab_requests_view', 'lab_requests_create', 'lab_requests_edit', 'lab_requests_delete'
                    ]
                ]);
            }
        }

        auth()->login($admin);
        return redirect('/dashboard')->with('success', 'Logged in as Demo Admin');
    } catch (Exception $e) {
        return response("Error: " . $e->getMessage(), 500);
    }
});

Route::get('/dev/login-doctor', function() {
    try {
        $doctor = \App\Models\User::where('role', 'doctor')->where('is_active', true)->first();
        if (!$doctor) {
            // Create doctor if doesn't exist
            $clinic = \App\Models\Clinic::first();
            if (!$clinic) {
                $clinic = \App\Models\Clinic::create([
                    'name' => 'Demo Clinic',
                    'email' => 'demo@clinic.com',
                    'phone' => '123456789',
                    'address' => 'Demo Address',
                    'is_active' => true,
                    'activation_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
                    'activated_at' => now(),
                    'max_users' => 50,
                ]);
            }

            $existingDoctor = \App\Models\User::where('username', 'doctor')->first();
            if ($existingDoctor) {
                $existingDoctor->update([
                    'email' => 'doctor@demo.clinic',
                    'password' => bcrypt('doctor123'),
                    'first_name' => 'Dr. John',
                    'last_name' => 'Smith',
                    'role' => 'doctor',
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                    'permissions' => [
                        'dashboard_view', 'dashboard_stats',
                        'patients_view', 'patients_create', 'patients_edit',
                        'prescriptions_view', 'prescriptions_create', 'prescriptions_edit',
                        'appointments_view', 'appointments_create', 'appointments_edit',
                        'medicines_view',
                        'nutrition_view', 'nutrition_create', 'nutrition_edit',
                        'lab_requests_view', 'lab_requests_create', 'lab_requests_edit',
                        'ai_advisory_view', 'ai_advisory_use'
                    ]
                ]);
                $doctor = $existingDoctor;
            } else {
                $doctor = \App\Models\User::create([
                    'username' => 'doctor',
                    'email' => 'doctor@demo.clinic',
                    'password' => bcrypt('doctor123'),
                    'first_name' => 'Dr. John',
                    'last_name' => 'Smith',
                    'role' => 'doctor',
                    'is_active' => true,
                    'activated_at' => now(),
                    'clinic_id' => $clinic->id,
                    'permissions' => [
                        'dashboard_view', 'dashboard_stats',
                        'patients_view', 'patients_create', 'patients_edit',
                        'prescriptions_view', 'prescriptions_create', 'prescriptions_edit',
                        'appointments_view', 'appointments_create', 'appointments_edit',
                        'medicines_view',
                        'nutrition_view', 'nutrition_create', 'nutrition_edit',
                        'lab_requests_view', 'lab_requests_create', 'lab_requests_edit',
                        'ai_advisory_view', 'ai_advisory_use'
                    ]
                ]);
            }
        }

        auth()->login($doctor);
        return redirect('/dashboard')->with('success', 'Logged in as Demo Doctor');
    } catch (Exception $e) {
        return response("Error: " . $e->getMessage(), 500);
    }
});

// Create demo users route
Route::get('/dev/create-demo-users', function () {
    try {
        // Create or get default clinic
        $clinic = \App\Models\Clinic::first();
        if (!$clinic) {
            $clinic = \App\Models\Clinic::create([
                'name' => 'Demo Clinic',
                'email' => 'demo@clinic.com',
                'phone' => '123456789',
                'address' => 'Demo Address',
                'is_active' => true,
                'activation_code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
                'activated_at' => now(),
                'max_users' => 50,
            ]);
        } else {
            $clinic->update([
                'is_active' => true,
                'activated_at' => now(),

            ]);
        }

        // Create or update admin user
        $adminUser = \App\Models\User::where('username', 'admin')->first();
        if (!$adminUser) {
            $adminUser = \App\Models\User::create([
                'username' => 'admin',
                'email' => 'admin@demo.clinic',
                'password' => bcrypt('admin123'),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'role' => 'admin',
                'is_active' => true,
                'activated_at' => now(),
                'clinic_id' => $clinic->id,
                'permissions' => [
                    'dashboard_view', 'dashboard_stats',
                    'patients_view', 'patients_create', 'patients_edit', 'patients_delete',
                    'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_delete',
                    'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete',
                    'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete',
                    'users_view', 'users_create', 'users_edit', 'users_delete',
                    'settings_view', 'settings_edit',
                    'finance_view', 'finance_create', 'finance_edit', 'finance_delete',
                    'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete',
                    'radiology_view', 'radiology_create', 'radiology_edit', 'radiology_delete',
                    'lab_requests_view', 'lab_requests_create', 'lab_requests_edit', 'lab_requests_delete'
                ]
            ]);
        } else {
            $adminUser->update([
                'password' => bcrypt('admin123'),
                'is_active' => true,
                'activated_at' => now(),
                'clinic_id' => $clinic->id,
            ]);
        }

        // Create or update doctor user
        $doctorUser = \App\Models\User::where('username', 'doctor')->first();
        if (!$doctorUser) {
            $doctorUser = \App\Models\User::create([
                'username' => 'doctor',
                'email' => 'doctor@demo.clinic',
                'password' => bcrypt('doctor123'),
                'first_name' => 'Dr. John',
                'last_name' => 'Smith',
                'role' => 'doctor',
                'is_active' => true,
                'activated_at' => now(),
                'clinic_id' => $clinic->id,
                'permissions' => [
                    'dashboard_view', 'dashboard_stats',
                    'patients_view', 'patients_create', 'patients_edit',
                    'prescriptions_view', 'prescriptions_create', 'prescriptions_edit',
                    'appointments_view', 'appointments_create', 'appointments_edit',
                    'medicines_view',
                    'nutrition_view', 'nutrition_create', 'nutrition_edit',
                    'lab_requests_view', 'lab_requests_create', 'lab_requests_edit'
                ]
            ]);
        } else {
            $doctorUser->update([
                'password' => bcrypt('doctor123'),
                'is_active' => true,
                'activated_at' => now(),
                'clinic_id' => $clinic->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Demo users created successfully!',
            'users' => [
                'admin' => [
                    'username' => 'admin',
                    'email' => 'admin@demo.clinic',
                    'password' => 'admin123',
                    'id' => $adminUser->id
                ],
                'doctor' => [
                    'username' => 'doctor',
                    'email' => 'doctor@demo.clinic',
                    'password' => 'doctor123',
                    'id' => $doctorUser->id
                ]
            ],
            'clinic' => [
                'name' => $clinic->name,
                'id' => $clinic->id
            ]
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
