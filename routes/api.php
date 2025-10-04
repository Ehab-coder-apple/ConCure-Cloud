<?php

// Temporarily disabled API routes - will be implemented later
// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\PatientController;
// use App\Http\Controllers\Api\RecommendationController;
// use App\Http\Controllers\Api\FoodCompositionController;
// use App\Http\Controllers\Api\FinanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API routes temporarily disabled - focusing on SaaS web interface
/*
// Public API routes
Route::prefix('v1')->group(function () {

    // Authentication
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Protected API routes
    Route::middleware(['auth:sanctum', 'activation'])->group(function () {

        // User info
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::post('/logout', [AuthController::class, 'logout']);

        // Patients API
        Route::apiResource('patients', PatientController::class);
        Route::post('/patients/{patient}/checkup', [PatientController::class, 'addCheckup']);
        Route::post('/patients/{patient}/upload', [PatientController::class, 'uploadFile']);
        Route::get('/patients/{patient}/history', [PatientController::class, 'history']);

        // Recommendations API
        Route::prefix('recommendations')->group(function () {
            Route::get('/lab-requests', [RecommendationController::class, 'labRequests']);
            Route::post('/lab-requests', [RecommendationController::class, 'storeLabRequest']);

            Route::get('/prescriptions', [RecommendationController::class, 'prescriptions']);
            Route::post('/prescriptions', [RecommendationController::class, 'storePrescription']);

            Route::get('/diet-plans', [RecommendationController::class, 'dietPlans']);
            Route::post('/diet-plans', [RecommendationController::class, 'storeDietPlan']);
        });

        // Food Composition API
        Route::get('/food-composition', [FoodCompositionController::class, 'index']);
        Route::get('/food-composition/search', [FoodCompositionController::class, 'search']);

        // Finance API (permission-aware)
        Route::middleware('can:manage-finance')->prefix('finance')->group(function () {
            Route::get('/invoices', [FinanceController::class, 'invoices']);
            Route::post('/invoices', [FinanceController::class, 'storeInvoice']);
            Route::get('/expenses', [FinanceController::class, 'expenses']);
            Route::post('/expenses', [FinanceController::class, 'storeExpense']);
            Route::get('/reports/cash-flow', [FinanceController::class, 'cashFlowReport']);
            Route::get('/reports/profit-loss', [FinanceController::class, 'profitLossReport']);
        });

        // Communication API
        Route::post('/send-whatsapp', [App\Http\Controllers\Api\CommunicationController::class, 'sendWhatsApp']);
        Route::post('/send-sms', [App\Http\Controllers\Api\CommunicationController::class, 'sendSMS']);
    });
});
*/

// Simple API health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'message' => 'ConCure SaaS API is running']);
});

// Debug route to check food translations
Route::get('/debug-food-translations/{dietPlanId}', function ($dietPlanId) {
    try {
        $dietPlan = \App\Models\DietPlan::with(['meals.foods.food'])->findOrFail($dietPlanId);

        $translationData = [];
        foreach ($dietPlan->meals as $meal) {
            foreach ($meal->foods as $mealFood) {
                if ($mealFood->food) {
                    $translationData[] = [
                        'food_id' => $mealFood->food->id,
                        'original_name' => $mealFood->food->name,
                        'name_translations' => $mealFood->food->name_translations,
                        'ku_bahdini_translation' => $mealFood->food->getNameInLanguage('ku_bahdini'),
                        'ku_sorani_translation' => $mealFood->food->getNameInLanguage('ku_sorani'),
                        'ar_translation' => $mealFood->food->getNameInLanguage('ar'),
                    ];
                }
            }
        }

        return response()->json([
            'diet_plan_id' => $dietPlanId,
            'total_foods' => count($translationData),
            'foods' => $translationData
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
