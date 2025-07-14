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
use App\Http\Controllers\MasterWelcomeController;
use App\Http\Controllers\SubscriptionController;
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

// Main Welcome Page (directs to both clinic and master portals)
Route::get('/', [MainWelcomeController::class, 'index'])->name('main.welcome');

// Clinic Portal Routes (Public)
Route::get('/clinic', [WelcomeController::class, 'index'])->name('welcome.index');
Route::get('/register', [WelcomeController::class, 'register'])->name('welcome.register');
Route::post('/register', [WelcomeController::class, 'store'])->name('welcome.store');
Route::get('/login', [WelcomeController::class, 'login'])->name('welcome.login');
Route::post('/login', [WelcomeController::class, 'authenticate'])->name('welcome.authenticate');
Route::post('/logout', [WelcomeController::class, 'logout'])->name('welcome.logout');

// Master Control Welcome Routes (Public)
Route::prefix('master-control')->name('master.welcome.')->group(function () {
    Route::get('/', [MasterWelcomeController::class, 'index'])->name('index');
    Route::get('/register', [MasterWelcomeController::class, 'register'])->name('register');
    Route::post('/register', [MasterWelcomeController::class, 'store'])->name('store');
    Route::get('/login', [MasterWelcomeController::class, 'login'])->name('login');
    Route::post('/login', [MasterWelcomeController::class, 'authenticate'])->name('authenticate');
    Route::post('/logout', [MasterWelcomeController::class, 'logout'])->name('logout');

    // Team management routes (requires program owner authentication)
    Route::middleware('auth')->group(function () {
        Route::get('/invite-team', [MasterWelcomeController::class, 'inviteTeam'])->name('invite-team');
        Route::post('/invite-team', [MasterWelcomeController::class, 'sendInvitation'])->name('send-invitation');
    });
});

// Legacy Authentication routes (for backward compatibility)
Route::get('/auth/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/auth/login', [LoginController::class, 'login']);
Route::post('/auth/logout', [LoginController::class, 'logout'])->name('logout');

// Public clinic activation routes
Route::get('/activate-clinic', [ClinicActivationController::class, 'showActivationForm'])->name('clinic.activate.form');
Route::post('/activate-clinic', [ClinicActivationController::class, 'activate'])->name('clinic.activate');
Route::post('/validate-activation-code', [ClinicActivationController::class, 'validateCode'])->name('clinic.validate-code');

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

// Subscription Management Routes
Route::middleware('auth')->prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/expired', [SubscriptionController::class, 'expired'])->name('expired');
    Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
    Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
    Route::post('/upgrade', [SubscriptionController::class, 'processUpgrade'])->name('process-upgrade');
    Route::get('/status', [SubscriptionController::class, 'status'])->name('status');
});

// Protected routes
Route::middleware(['auth', 'activation'])->group(function () {
    
    // Tenant Dashboard (Clinic Users Only)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Patient Management
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/create', [PatientController::class, 'create'])->name('create');
        Route::post('/', [PatientController::class, 'store'])->name('store');

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
        Route::get('/create/weight-loss', [App\Http\Controllers\NutritionController::class, 'createWeightLoss'])->name('create.weight-loss');
        Route::get('/create/muscle-gain', [App\Http\Controllers\NutritionController::class, 'createMuscleGain'])->name('create.muscle-gain');
        Route::get('/create/diabetic', [App\Http\Controllers\NutritionController::class, 'createDiabetic'])->name('create.diabetic');

        // Parameterized routes (must be after specific routes)
        Route::get('/{dietPlan}', [App\Http\Controllers\NutritionController::class, 'show'])->name('show');
        Route::get('/{dietPlan}/edit', [App\Http\Controllers\NutritionController::class, 'edit'])->name('edit');
        Route::put('/{dietPlan}', [App\Http\Controllers\NutritionController::class, 'update'])->name('update');
        Route::delete('/{dietPlan}', [App\Http\Controllers\NutritionController::class, 'destroy'])->name('destroy');
        Route::get('/{dietPlan}/pdf', [App\Http\Controllers\NutritionController::class, 'pdf'])->name('pdf');
        Route::get('/{dietPlan}/word', [App\Http\Controllers\NutritionController::class, 'downloadWord'])->name('word');

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
        Route::get('/lab-requests/{labRequest}/print', [RecommendationController::class, 'printLabRequest'])->name('lab-requests.print');
        Route::patch('/lab-requests/{labRequest}/status', [RecommendationController::class, 'updateLabRequestStatus'])->name('lab-requests.update-status');



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
                        'activated_at' => now(),
                        'subscription_expires_at' => now()->addYear(),
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
                        'activated_at' => now(),
                        'subscription_expires_at' => now()->addYear(),
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
            if (!in_array($user->role, ['admin', 'program_owner'])) {
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
        Route::get('/invoices/{invoice}/pdf', [FinanceController::class, 'generateInvoicePDF'])->name('invoices.pdf');
        
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

        // Activation codes (Admin only for now)
        Route::get('/activation-codes', [UserController::class, 'activationCodes'])->name('activation-codes')->middleware('role:admin,program_owner');
        Route::post('/activation-codes', [UserController::class, 'generateActivationCode'])->name('activation-codes.generate')->middleware('role:admin,program_owner');
        Route::delete('/activation-codes/{code}', [UserController::class, 'deleteActivationCode'])->name('activation-codes.delete')->middleware('role:admin,program_owner');
        Route::patch('/activation-codes/{code}/extend', [UserController::class, 'extendActivationCode'])->name('activation-codes.extend')->middleware('role:admin,program_owner');
    });
    
    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
        Route::delete('/logo', [SettingsController::class, 'deleteLogo'])->name('delete-logo');

        // Audit logs (Admin only)
        Route::get('/audit-logs', [SettingsController::class, 'auditLogs'])->name('audit-logs')->middleware('role:admin');
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
                    'subscription_expires_at' => now()->addYear(),
                    'max_users' => 50,
                ]);
            } else {
                $clinic->update([
                    'is_active' => true,
                    'activated_at' => now(),
                    'subscription_expires_at' => now()->addYear(),
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
                'subscription_expires_at' => now()->addYear()
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
                    'subscription_expires_at' => now()->addYear(),
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
                    'activated_at' => now(),
                    'subscription_expires_at' => now()->addYear(),
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
                    'lab_requests_view', 'lab_requests_create', 'lab_requests_edit', 'lab_requests_delete'
                ]
            ]);
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
                    'activated_at' => now(),
                    'subscription_expires_at' => now()->addYear(),
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
                'activated_at' => now(),
                'subscription_expires_at' => now()->addYear(),
                'max_users' => 50,
            ]);
        } else {
            $clinic->update([
                'is_active' => true,
                'activated_at' => now(),
                'subscription_expires_at' => now()->addYear(),
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
