<?php

namespace App\Http\Controllers;

use App\Models\DietPlan;
use App\Models\DietPlanMeal;
use App\Models\DietPlanMealFood;
use App\Models\DietPlanWeightRecord;
use App\Models\Food;
use App\Models\FoodGroup;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\KurdishFontService;
use App\Services\PdfKurdishFontService;
use App\Services\DomPdfFontLoader;
use App\Services\WordDocumentService;

class NutritionController extends Controller
{
    /**
     * Display a listing of nutrition plans.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user can view nutrition plans
        if (!$user->canViewNutritionPlans()) {
            abort(403, 'You do not have permission to view nutrition plans.');
        }

        $query = DietPlan::with(['patient', 'doctor']);

        // Filter by clinic
        $query->whereHas('patient', function ($q) use ($user) {
            $q->where('clinic_id', $user->clinic_id);
        });

        // Filter by doctor if user is a doctor or nutritionist
        if (in_array($user->role, ['doctor', 'nutritionist'])) {
            $query->where('doctor_id', $user->id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('goal')) {
            $query->where('goal', $request->goal);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('plan_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('patient_id', 'like', "%{$search}%");
                  });
            });
        }

        $nutritionPlans = $query->latest()->paginate(15);

        // Get statistics
        $stats = [
            'total' => DietPlan::whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            })->count(),
            'active' => DietPlan::whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            })->where('status', 'active')->count(),
            'completed' => DietPlan::whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            })->where('status', 'completed')->count(),
        ];

        // Get patients for filter dropdown
        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        return view('nutrition.index', compact('nutritionPlans', 'stats', 'patients'));
    }

    /**
     * Show the form for creating a new nutrition plan.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!$user->canCreateNutritionPlans()) {
            abort(403, 'You do not have permission to create nutrition plans.');
        }

        // Get patients for dropdown
        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        // Get food groups and foods
        $foodGroups = FoodGroup::with(['foods' => function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('is_custom', false)
                  ->orWhere('clinic_id', $user->clinic_id);
            })->where('is_active', true);
        }])->get();

        // Pre-select patient if provided
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::where('id', $request->patient_id)
                                    ->where('clinic_id', $user->clinic_id)
                                    ->first();
        }

        return view('nutrition.create', compact('patients', 'foodGroups', 'selectedPatient'));
    }

    /**
     * Show the enhanced form for creating a new nutrition plan with detailed meal planning.
     */
    public function createEnhanced(Request $request)
    {
        $user = Auth::user();

        if (!$user->canCreateNutritionPlans()) {
            abort(403, 'You do not have permission to create nutrition plans.');
        }

        // Check if editing existing plan
        $dietPlan = null;
        if ($request->filled('edit')) {
            $editId = $request->edit;

            // First check if the diet plan exists at all
            $planExists = DietPlan::where('id', $editId)->exists();
            if (!$planExists) {
                return redirect()->route('nutrition.index')->with('error', "Nutrition plan with ID {$editId} not found.");
            }

            // Then check if user has access to it
            $dietPlan = DietPlan::with(['patient', 'meals.foods.food'])
                              ->where('id', $editId)
                              ->whereHas('patient', function($query) use ($user) {
                                  $query->where('clinic_id', $user->clinic_id);
                              })
                              ->first();

            if (!$dietPlan) {
                return redirect()->route('nutrition.index')->with('error', 'You do not have permission to edit this nutrition plan.');
            }
        }

        // Get patients for dropdown
        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        // Get food groups for the food selection modal
        $foodGroups = FoodGroup::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        // Pre-select patient if provided or from existing plan
        $selectedPatient = null;
        if ($dietPlan) {
            $selectedPatient = $dietPlan->patient;
        } elseif ($request->filled('patient_id')) {
            $selectedPatient = Patient::where('id', $request->patient_id)
                                    ->where('clinic_id', $user->clinic_id)
                                    ->first();
        }

        return view('nutrition.create-enhanced', compact('patients', 'foodGroups', 'selectedPatient', 'dietPlan'));
    }

    /**
     * Store a newly created nutrition plan.
     */
    public function store(Request $request)
    {
        \Log::info('NutritionController::store method called');
        $user = Auth::user();

        if (!$user->canCreateNutritionPlans()) {
            abort(403, 'You do not have permission to create nutrition plans.');
        }



        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal' => 'required|in:weight_loss,weight_gain,maintenance,muscle_gain,diabetic,health_improvement,other',
            'goal_description' => 'nullable|string',
            'duration_days' => 'nullable|integer|min:1|max:365',
            'target_calories' => 'nullable|numeric|min:500|max:5000',
            'target_protein' => 'nullable|numeric|min:0|max:500',
            'target_carbs' => 'nullable|numeric|min:0|max:1000',
            'target_fat' => 'nullable|numeric|min:0|max:300',
            'initial_weight' => 'nullable|numeric|min:20|max:500',
            'target_weight' => 'nullable|numeric|min:20|max:500',
            'initial_height' => 'nullable|numeric|min:100|max:250',
            'weekly_weight_goal' => 'nullable|numeric|min:-2|max:2',
            'instructions' => 'nullable|string',
            'restrictions' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'meal_data' => 'nullable|string', // JSON string of meal data (legacy)
            'meal_options' => 'nullable|string', // JSON string of meal options data
            'weekly_meal_data' => 'nullable|string', // JSON string of weekly meal data
            'meals' => 'nullable|array',
            'meals.*.name' => 'required_with:meals|string|max:255',
            'meals.*.time' => 'required_with:meals|string|max:10',
            'meals.*.day_number' => 'required_with:meals|integer|min:1',
            'meals.*.foods' => 'nullable|array',
            'meals.*.foods.*.food_id' => 'required_with:meals.*.foods|exists:foods,id',
            'meals.*.foods.*.quantity' => 'required_with:meals.*.foods|numeric|min:0.1',
            'meals.*.foods.*.unit' => 'required_with:meals.*.foods|string|max:50',
        ]);

        // Verify patient belongs to clinic
        $patient = Patient::where('id', $request->patient_id)
                         ->where('clinic_id', $user->clinic_id)
                         ->firstOrFail();

        DB::beginTransaction();

        try {
            // Create nutrition plan
            $nutritionPlan = DietPlan::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'goal' => $request->goal,
                'goal_description' => $request->goal_description,
                'duration_days' => $request->duration_days,
                'target_calories' => $request->target_calories,
                'target_protein' => $request->target_protein,
                'target_carbs' => $request->target_carbs,
                'target_fat' => $request->target_fat,
                'initial_weight' => $request->initial_weight,
                'target_weight' => $request->target_weight,
                'initial_height' => $request->initial_height,
                'weekly_weight_goal' => $request->weekly_weight_goal,
                'instructions' => $request->instructions,
                'restrictions' => $request->restrictions,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'active',
            ]);

            // Initialize weight tracking from patient data if not provided
            $nutritionPlan->initializeWeightTracking();

            // Calculate initial BMI and other weight-related fields
            if ($nutritionPlan->initial_weight && $nutritionPlan->initial_height) {
                $initialBmi = Patient::calculateBMI($nutritionPlan->initial_weight, $nutritionPlan->initial_height);
                $updateData = [
                    'initial_bmi' => $initialBmi,
                    'current_weight' => $nutritionPlan->initial_weight,
                    'current_bmi' => $initialBmi,
                ];

                // Calculate target BMI if target weight is set
                if ($nutritionPlan->target_weight) {
                    $updateData['target_bmi'] = Patient::calculateBMI($nutritionPlan->target_weight, $nutritionPlan->initial_height);
                    $updateData['weight_goal_kg'] = $nutritionPlan->target_weight - $nutritionPlan->initial_weight;
                }

                $nutritionPlan->update($updateData);
            }

            // Create initial weight record
            if ($nutritionPlan->initial_weight) {
                $nutritionPlan->addWeightRecord([
                    'weight' => $nutritionPlan->initial_weight,
                    'height' => $nutritionPlan->initial_height,
                    'notes' => 'Initial weight record at plan start',
                    'record_date' => $nutritionPlan->start_date,
                    'recorded_by' => $user->id,
                ]);
            }



            // Handle enhanced meal data from detailed form (single day)
            if ($request->filled('meal_data')) {
                $mealData = json_decode($request->meal_data, true);

                // Debug: Log the received meal data (can be removed in production)
                \Log::info('Received meal data:', [
                    'raw_meal_data' => $request->meal_data,
                    'decoded_meal_data' => $mealData
                ]);

                if ($mealData && is_array($mealData)) {
                    $mealTypes = [
                        'breakfast' => ['name' => 'Breakfast', 'time' => '08:00'],
                        'lunch' => ['name' => 'Lunch', 'time' => '12:00'],
                        'dinner' => ['name' => 'Dinner', 'time' => '18:00'],
                        'snacks' => ['name' => 'Snacks', 'time' => '15:00']
                    ];

                    foreach ($mealData as $mealType => $foods) {
                        \Log::info("Processing meal type: {$mealType}", ['foods_count' => count($foods)]);

                        if (!empty($foods) && isset($mealTypes[$mealType])) {
                            $meal = $nutritionPlan->meals()->create([
                                'meal_type' => $mealType === 'snacks' ? 'snack_1' : $mealType,
                                'meal_name' => $mealTypes[$mealType]['name'],
                                'suggested_time' => $mealTypes[$mealType]['time'],
                                'day_number' => 1, // Default to day 1
                            ]);

                            \Log::info("Created meal: {$meal->id}", [
                                'meal_type' => $meal->meal_type,
                                'meal_name' => $meal->meal_name
                            ]);

                            // Add foods to meal
                            foreach ($foods as $foodData) {
                                $food = Food::find($foodData['id']);
                                // Use displayName if available (translated name), otherwise fall back to name
                                $foodName = $foodData['displayName'] ?? $foodData['name'] ?? ($food ? $food->name : '');
                                $mealFood = $meal->foods()->create([
                                    'food_id' => $foodData['id'],
                                    'food_name' => $foodName,
                                    'quantity' => $foodData['quantity'],
                                    'unit' => $foodData['unit'],
                                    'preparation_notes' => $foodData['notes'] ?? null,
                                ]);

                                \Log::info("Added food to meal: {$mealFood->id}", [
                                    'food_name' => $foodName,
                                    'quantity' => $foodData['quantity'],
                                    'unit' => $foodData['unit']
                                ]);
                            }
                        } else {
                            \Log::warning("Skipped meal type: {$mealType}", [
                                'foods_empty' => empty($foods),
                                'meal_type_exists' => isset($mealTypes[$mealType])
                            ]);
                        }
                    }
                }
            }

            // Handle enhanced meal options data (multiple options per meal type)
            if ($request->filled('meal_options')) {
                $mealOptionsData = json_decode($request->meal_options, true);

                // Debug: Log the received meal options data
                \Log::info('Received meal options data:', [
                    'raw_meal_options' => $request->meal_options,
                    'decoded_meal_options' => $mealOptionsData
                ]);

                // Additional debug: Check the structure of each meal type
                foreach ($mealOptionsData as $mealType => $options) {
                    \Log::info("Meal type {$mealType} structure:", [
                        'options_count' => count($options),
                        'first_option_structure' => !empty($options[0]) ? array_keys($options[0]) : 'No options',
                        'first_option_foods' => !empty($options[0]['foods']) ? count($options[0]['foods']) : 0,
                        'first_food_structure' => !empty($options[0]['foods'][0]) ? array_keys($options[0]['foods'][0]) : 'No foods'
                    ]);
                }

                if ($mealOptionsData && is_array($mealOptionsData)) {
                    foreach ($mealOptionsData as $mealType => $options) {
                        \Log::info("Processing meal type: {$mealType}", ['options_count' => count($options)]);

                        if (!empty($options) && is_array($options)) {
                            foreach ($options as $option) {
                                // Create meal option
                                $meal = $nutritionPlan->meals()->create([
                                    'meal_type' => $mealType === 'snacks' ? 'snack_1' : $mealType,
                                    'option_number' => $option['option_number'] ?? 1,
                                    'is_option_based' => true,
                                    'option_description' => $option['option_description'] ?? "Option {$option['option_number']}",
                                    'meal_name' => $option['option_description'] ?? "Option {$option['option_number']}",
                                    'day_number' => null, // Not used in option-based system
                                ]);

                                \Log::info("Created meal option: {$meal->id}", [
                                    'meal_type' => $meal->meal_type,
                                    'option_number' => $meal->option_number,
                                    'option_description' => $meal->option_description
                                ]);

                                // Add foods to the meal option
                                if (!empty($option['foods']) && is_array($option['foods'])) {
                                    foreach ($option['foods'] as $foodData) {
                                        // Debug: Log the food data structure
                                        \Log::info('Processing food data:', $foodData);

                                        try {
                                            $mealFood = $meal->foods()->create([
                                                'food_id' => $foodData['food_id'] ?? null,
                                                'food_name' => $foodData['food_name'] ?? $foodData['displayName'] ?? 'Unknown Food',
                                                'quantity' => $foodData['quantity'] ?? 0,
                                                'unit' => $foodData['unit'] ?? 'g',
                                                'preparation_notes' => $foodData['preparation_notes'] ?? null,
                                            ]);
                                        } catch (\Exception $e) {
                                            \Log::error('Error creating meal food:', [
                                                'error' => $e->getMessage(),
                                                'food_data' => $foodData,
                                                'available_keys' => array_keys($foodData)
                                            ]);
                                            throw $e;
                                        }

                                        \Log::info("Added food to meal option: {$mealFood->id}", [
                                            'food_name' => $mealFood->food_name,
                                            'quantity' => $foodData['quantity'],
                                            'unit' => $foodData['unit']
                                        ]);
                                    }
                                }
                            }
                        } else {
                            \Log::warning("Skipped meal type: {$mealType}", [
                                'options_empty' => empty($options)
                            ]);
                        }
                    }
                }
            }

            // Add meals if provided (legacy format)
            if ($request->filled('meals')) {
                foreach ($request->meals as $mealData) {
                    $meal = $nutritionPlan->meals()->create([
                        'meal_name' => $mealData['name'],
                        'suggested_time' => $mealData['time'],
                        'day_number' => $mealData['day_number'],
                        'instructions' => $mealData['instructions'] ?? null,
                    ]);

                    // Add foods to meal if provided
                    if (!empty($mealData['foods'])) {
                        foreach ($mealData['foods'] as $foodData) {
                            $food = Food::find($foodData['food_id']);
                            // Use displayName if available (translated name), otherwise fall back to name
                            $foodName = $foodData['displayName'] ?? $foodData['name'] ?? ($food ? $food->name : '');
                            $meal->foods()->create([
                                'food_id' => $foodData['food_id'],
                                'food_name' => $foodName,
                                'quantity' => $foodData['quantity'],
                                'unit' => $foodData['unit'],
                                'preparation_notes' => $foodData['notes'] ?? null,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('nutrition.show', $nutritionPlan)
                           ->with('success', 'Nutrition plan created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                        ->with('error', 'Failed to create nutrition plan. Please try again.');
        }
    }

    /**
     * Display the specified nutrition plan.
     */
    public function show(DietPlan $dietPlan)
    {
        $user = Auth::user();

        // Check access
        if ($dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to nutrition plan.');
        }

        $dietPlan->load(['patient', 'doctor', 'meals.foods.food']);

        // Calculate nutritional totals
        $nutritionalTotals = $this->calculateNutritionalTotals($dietPlan);

        return view('nutrition.show', compact('dietPlan', 'nutritionalTotals'));
    }

    /**
     * Show the form for editing the specified nutrition plan.
     */
    public function edit(DietPlan $dietPlan)
    {
        $user = Auth::user();

        // Check access and permissions
        if ($dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to nutrition plan.');
        }

        if (!$user->canEditNutritionPlans()) {
            abort(403, 'You do not have permission to edit nutrition plans.');
        }

        if (!$dietPlan->canBeModified()) {
            return back()->with('error', 'This nutrition plan cannot be modified.');
        }

        // Get patients for dropdown
        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        // Get food groups and foods
        $foodGroups = FoodGroup::with(['foods' => function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('is_custom', false)
                  ->orWhere('clinic_id', $user->clinic_id);
            })->where('is_active', true);
        }])->get();

        $dietPlan->load(['meals.foods.food']);

        return view('nutrition.edit', compact('dietPlan', 'patients', 'foodGroups'));
    }

    /**
     * Show the enhanced form for editing the specified nutrition plan.
     */
    public function editEnhanced(DietPlan $dietPlan)
    {
        $user = Auth::user();

        // Check access and permissions
        if ($dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to nutrition plan.');
        }

        if (!$user->canEditNutritionPlans()) {
            abort(403, 'You do not have permission to edit nutrition plans.');
        }

        if (!$dietPlan->canBeModified()) {
            return back()->with('error', 'This nutrition plan cannot be modified.');
        }

        // Get patients for dropdown
        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        // Get food groups and foods
        $foodGroups = FoodGroup::with(['foods' => function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('is_custom', false)
                  ->orWhere('clinic_id', $user->clinic_id);
            })->where('is_active', true);
        }])->get();

        // Load the diet plan with all related data
        $dietPlan->load(['patient', 'meals.foods.food']);

        // Set selected patient for the form
        $selectedPatient = $dietPlan->patient;

        return view('nutrition.create-enhanced', compact('dietPlan', 'patients', 'foodGroups', 'selectedPatient'));
    }

    /**
     * Update the specified nutrition plan.
     */
    public function update(Request $request, DietPlan $dietPlan)
    {
        $user = Auth::user();

        // Check access and permissions
        if ($dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to nutrition plan.');
        }

        if (!$user->canEditNutritionPlans()) {
            abort(403, 'You do not have permission to update nutrition plans.');
        }

        if (!$dietPlan->canBeModified()) {
            return back()->with('error', 'This nutrition plan cannot be modified.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal' => 'required|in:weight_loss,weight_gain,maintenance,muscle_gain,diabetic,health_improvement,other',
            'goal_description' => 'nullable|string',
            'duration_days' => 'nullable|integer|min:1|max:365',
            'target_calories' => 'nullable|numeric|min:500|max:5000',
            'target_protein' => 'nullable|numeric|min:0|max:500',
            'target_carbs' => 'nullable|numeric|min:0|max:1000',
            'target_fat' => 'nullable|numeric|min:0|max:300',
            'instructions' => 'nullable|string',
            'restrictions' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,completed,cancelled',
            'meal_options' => 'nullable|string', // JSON string of meal options data
        ]);

        $dietPlan->update($request->only([
            'title', 'description', 'goal', 'goal_description', 'duration_days',
            'target_calories', 'target_protein', 'target_carbs', 'target_fat',
            'instructions', 'restrictions', 'start_date', 'end_date', 'status'
        ]));

        // Debug: Check if meal_options is present in request
        \Log::info('Update request debug:', [
            'diet_plan_id' => $dietPlan->id,
            'has_meal_options' => $request->has('meal_options'),
            'meal_options_filled' => $request->filled('meal_options'),
            'meal_options_length' => $request->has('meal_options') ? strlen($request->meal_options) : 0,
            'all_request_keys' => array_keys($request->all())
        ]);

        // Handle meal options data update
        if ($request->filled('meal_options')) {
            $mealOptionsData = json_decode($request->meal_options, true);

            // Debug: Log the received meal options data
            \Log::info('Updating meal options data:', [
                'diet_plan_id' => $dietPlan->id,
                'raw_meal_options' => $request->meal_options,
                'decoded_meal_options' => $mealOptionsData,
                'json_decode_error' => json_last_error_msg()
            ]);

            if ($mealOptionsData && is_array($mealOptionsData)) {
                // Delete existing meals for this diet plan
                $dietPlan->meals()->delete();

                // Process each meal type and its options
                foreach ($mealOptionsData as $mealType => $options) {
                    if (!empty($options) && is_array($options)) {
                        foreach ($options as $optionIndex => $option) {
                            if (!empty($option['foods']) && is_array($option['foods'])) {
                                // Create a meal for this option
                                $meal = $dietPlan->meals()->create([
                                    'meal_type' => $mealType,
                                    'day_number' => 1, // Single day plan
                                    'option_number' => $optionIndex + 1,
                                ]);

                                // Add foods to this meal
                                foreach ($option['foods'] as $food) {
                                    $meal->foods()->create([
                                        'food_id' => $food['food_id'] ?? null,
                                        'food_name' => $food['food_name'] ?? $food['displayName'] ?? 'Unknown Food',
                                        'quantity' => $food['quantity'] ?? 100,
                                        'unit' => $food['unit'] ?? 'g',
                                        'preparation_notes' => $food['preparation_notes'] ?? $food['notes'] ?? null,
                                    ]);
                                }

                                \Log::info("Created meal option for {$mealType}", [
                                    'meal_id' => $meal->id,
                                    'option_number' => $optionIndex + 1,
                                    'foods_count' => count($option['foods'])
                                ]);
                            }
                        }
                    }
                }
            }
        } else {
            \Log::warning('No meal options data received in update request', [
                'diet_plan_id' => $dietPlan->id,
                'request_has_meal_options' => $request->has('meal_options'),
                'meal_options_value' => $request->get('meal_options', 'NOT_SET')
            ]);
        }

        return redirect()->route('nutrition.show', $dietPlan)
                       ->with('success', 'Nutrition plan updated successfully.');
    }

    /**
     * Remove the specified nutrition plan.
     */
    public function destroy(DietPlan $dietPlan)
    {
        $user = Auth::user();

        // Check access and permissions
        if ($dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to nutrition plan.');
        }

        if (!$user->canDeleteNutritionPlans()) {
            abort(403, 'You do not have permission to delete nutrition plans.');
        }

        $dietPlan->delete();

        return redirect()->route('nutrition.index')
                       ->with('success', 'Nutrition plan deleted successfully.');
    }

    /**
     * Generate PDF for nutrition plan.
     */
    public function pdf(DietPlan $dietPlan)
    {
        $user = Auth::user();

        // Check access
        if ($dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to nutrition plan.');
        }

        $dietPlan->load(['patient', 'doctor', 'meals.foods.food']);

        // Debug: Log meal data for PDF generation
        \Log::info("PDF Generation - Diet Plan {$dietPlan->id}", [
            'meals_count' => $dietPlan->meals->count(),
            'meals' => $dietPlan->meals->map(function($meal) {
                return [
                    'id' => $meal->id,
                    'meal_type' => $meal->meal_type,
                    'meal_name' => $meal->meal_name,
                    'foods_count' => $meal->foods->count(),
                    'foods' => $meal->foods->map(function($food) {
                        return [
                            'food_name' => $food->food_name,
                            'quantity' => $food->quantity,
                            'unit' => $food->unit
                        ];
                    })
                ];
            })
        ]);

        $nutritionalTotals = $this->calculateNutritionalTotals($dietPlan);

        // Use font loader for proper Kurdish font support
        $fontLoader = new DomPdfFontLoader();
        $fontLoader->loadFonts();

        // Process Kurdish text with Arabic shaping
        $this->processKurdishTextWithShaping($dietPlan);

        // Create configured DomPDF instance
        $dompdf = $fontLoader->createConfiguredDomPdf();

        // Check if this is a flexible meal plan (option-based)
        $isFlexiblePlan = $dietPlan->meals()->where('is_option_based', true)->exists();

        // Choose appropriate template
        $template = $isFlexiblePlan ? 'nutrition.pdf-flexible-options' : 'nutrition.pdf-simple';

        // Generate HTML content with appropriate template
        $html = view($template, compact('dietPlan', 'nutritionalTotals'))->render();

        // Load and render PDF
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="nutrition-plan-' . $dietPlan->plan_number . '.pdf"'
        ]);
    }

    /**
     * Download nutrition plan as Word document with proper Kurdish font support
     */
    public function downloadWord(DietPlan $dietPlan)
    {
        $user = Auth::user();

        // Check access and permissions
        if ($dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to nutrition plan.');
        }

        $dietPlan->load(['patient', 'doctor', 'meals.foods.food']);
        $nutritionalTotals = $this->calculateNutritionalTotals($dietPlan);

        // Use Word document service
        $wordService = new WordDocumentService();
        $htmlContent = $wordService->generateNutritionPlan($dietPlan, $nutritionalTotals);

        $filename = "nutrition-plan-{$dietPlan->plan_number}.doc";

        return response($htmlContent, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }



    /**
     * Show the form for creating a flexible nutrition plan with meal options.
     */
    public function createFlexible(Request $request)
    {
        $user = Auth::user();

        if (!$user->canCreateNutritionPlans()) {
            abort(403, 'You do not have permission to create nutrition plans.');
        }

        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        // Pre-select patient if provided
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::where('id', $request->patient_id)
                                    ->where('clinic_id', $user->clinic_id)
                                    ->first();
        }

        return view('nutrition.create-flexible', compact('patients', 'selectedPatient'));
    }

    /**
     * Store a flexible nutrition plan with meal options.
     */
    public function storeFlexible(Request $request)
    {
        \Log::info('NutritionController::storeFlexible method called');
        $user = Auth::user();

        if (!$user->canCreateNutritionPlans()) {
            abort(403, 'You do not have permission to create nutrition plans.');
        }

        // Validate basic plan data
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'title' => 'required|string|max:255',
            'goal' => 'required|in:' . implode(',', array_keys(DietPlan::GOALS)),
            'target_calories' => 'nullable|numeric|min:500|max:5000',
            'duration_days' => 'nullable|integer|min:1|max:365',
            'instructions' => 'nullable|string',
            'restrictions' => 'nullable|string',
            'meal_options' => 'required|json',
        ]);

        // Verify patient belongs to user's clinic
        $patient = Patient::where('id', $request->patient_id)
                         ->where('clinic_id', $user->clinic_id)
                         ->firstOrFail();

        try {
            DB::beginTransaction();

            // Create nutrition plan
            $nutritionPlan = DietPlan::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $user->id,
                'title' => $request->title,
                'description' => 'Flexible meal plan with multiple options for each meal type',
                'goal' => $request->goal,
                'duration_days' => $request->duration_days,
                'target_calories' => $request->target_calories,
                'instructions' => $request->instructions,
                'restrictions' => $request->restrictions,
                'start_date' => now()->toDateString(),
            ]);

            // Parse meal options data
            $mealOptionsData = json_decode($request->meal_options, true);

            if ($mealOptionsData && is_array($mealOptionsData)) {
                foreach ($mealOptionsData as $mealType => $options) {
                    if (!empty($options) && is_array($options)) {
                        foreach ($options as $option) {
                            // Create meal option
                            $meal = $nutritionPlan->meals()->create([
                                'meal_type' => $mealType === 'snacks' ? 'snack_1' : $mealType,
                                'option_number' => $option['option_number'] ?? 1,
                                'is_option_based' => true,
                                'option_description' => $option['option_description'] ?? "Option {$option['option_number']}",
                                'meal_name' => $option['option_description'] ?? "Option {$option['option_number']}",
                                'day_number' => null, // Not used in option-based system
                            ]);

                            // Add foods to the meal option
                            if (!empty($option['foods']) && is_array($option['foods'])) {
                                foreach ($option['foods'] as $foodData) {
                                    $meal->foods()->create([
                                        'food_id' => $foodData['food_id'] ?? null,
                                        'food_name' => $foodData['food_name'] ?? $foodData['displayName'] ?? 'Unknown Food',
                                        'quantity' => $foodData['quantity'] ?? 0,
                                        'unit' => $foodData['unit'] ?? 'g',
                                        'preparation_notes' => $foodData['preparation_notes'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('nutrition.show', $nutritionPlan)
                           ->with('success', 'Flexible nutrition plan created successfully! Patients can choose from the meal options you\'ve provided.');

        } catch (\Exception $e) {
            DB::rollback();

            return back()->withInput()
                        ->withErrors(['error' => 'Failed to create nutrition plan: ' . $e->getMessage()]);
        }
    }

    /**
     * Process Kurdish text in meals for proper PDF rendering
     */
    private function processKurdishTextInMeals($dietPlan, KurdishFontService $fontService)
    {
        foreach ($dietPlan->meals as $meal) {
            foreach ($meal->foods as $mealFood) {
                $foodName = $mealFood->food_name;
                $processedText = $fontService->processText($foodName);
                $mealFood->food_name = $processedText;
            }
        }
    }

    /**
     * Process Kurdish text for enhanced PDF rendering
     */
    private function processKurdishTextForPdf($dietPlan, PdfKurdishFontService $pdfService)
    {
        foreach ($dietPlan->meals as $meal) {
            foreach ($meal->foods as $mealFood) {
                $foodName = $mealFood->food_name;
                $processedText = $pdfService->processKurdishText($foodName);
                $mealFood->food_name = $processedText;
            }
        }
    }

    /**
     * Process Kurdish text with advanced Arabic shaping for PDF
     */
    private function processKurdishTextWithShaping($dietPlan)
    {
        // Check if ArPHP library is available
        if (!class_exists('\ArPHP\I18N\Arabic')) {
            // Skip Arabic processing if library is not installed
            return;
        }

        try {
            $arabic = new \ArPHP\I18N\Arabic();

        foreach ($dietPlan->meals as $meal) {
            foreach ($meal->foods as $mealFood) {
                $foodName = $mealFood->food_name;

                // Check if text contains Kurdish/Arabic characters
                if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $foodName)) {
                    try {
                        // Use multiple Arabic processing methods for better shaping

                        // Method 1: Standard glyph shaping
                        $processedText = $arabic->utf8Glyphs($foodName);

                        // Method 2: Try Arabic string processing for better connection
                        if ($processedText === $foodName || empty($processedText)) {
                            // Alternative processing method
                            $arabic->setInputCharset('UTF-8');
                            $arabic->setOutputCharset('UTF-8');
                            $processedText = $arabic->utf8Glyphs($foodName, 50, false);
                        }

                        // Method 3: Manual Unicode normalization if still not working
                        if ($processedText === $foodName || empty($processedText)) {
                            // Normalize Unicode and try again
                            $normalizedText = \Normalizer::normalize($foodName, \Normalizer::FORM_C);
                            $processedText = $arabic->utf8Glyphs($normalizedText);
                        }

                        // Use the best result
                        if ($processedText && $processedText !== $foodName && strlen($processedText) > 0) {
                            $mealFood->food_name = $processedText;
                        } else {
                            // If all processing fails, keep original
                            $mealFood->food_name = $foodName;
                        }

                    } catch (\Exception $e) {
                        // Keep original text if processing fails
                        $mealFood->food_name = $foodName;
                    }
                } else {
                    // For non-Kurdish text, keep as is
                    $mealFood->food_name = $foodName;
                }
            }
        }
        } catch (\Exception $e) {
            // Log error but don't fail the export
            \Log::warning('Arabic text processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Create specialized nutrition plan templates.
     */
    public function templates()
    {
        $user = Auth::user();

        if (!$user->canViewNutritionPlans()) {
            abort(403, 'You do not have permission to view nutrition templates.');
        }

        return view('nutrition.templates');
    }

    /**
     * Calculate target calories for a patient based on their goals.
     */
    public function calculateTargetCalories(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'goal' => 'required|in:weight_loss,weight_gain,maintenance,muscle_gain',
            'weekly_weight_goal' => 'nullable|numeric|min:0.1|max:2.0',
            'activity_level' => 'required|in:sedentary,light,moderate,active,very_active',
            'target_weight' => 'nullable|numeric|min:30|max:300'
        ]);

        $user = Auth::user();
        $patient = Patient::where('clinic_id', $user->clinic_id)->findOrFail($request->patient_id);

        // Default weekly weight goals based on goal type
        $weeklyWeightGoal = $request->weekly_weight_goal;
        if (!$weeklyWeightGoal) {
            switch ($request->goal) {
                case 'weight_loss':
                    $weeklyWeightGoal = 0.5; // 0.5kg per week (safe rate)
                    break;
                case 'weight_gain':
                case 'muscle_gain':
                    $weeklyWeightGoal = 0.3; // 0.3kg per week
                    break;
                case 'maintenance':
                    $weeklyWeightGoal = 0;
                    break;
            }
        }

        // Calculate target calories
        $calorieData = $patient->calculateTargetCalories(
            $request->goal,
            $weeklyWeightGoal,
            $request->activity_level
        );

        if (!$calorieData) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate calories. Please ensure patient has complete data (weight, height, age, gender).'
            ], 400);
        }

        // Calculate macronutrient distribution
        $macros = $this->calculateMacronutrients($calorieData['target_calories'], $request->goal);

        // Calculate time to reach target weight if provided
        $timeToGoal = null;
        if ($request->target_weight && $weeklyWeightGoal > 0) {
            $weightDifference = abs($request->target_weight - $patient->weight);
            $weeksToGoal = $weightDifference / $weeklyWeightGoal;
            $timeToGoal = [
                'weeks' => round($weeksToGoal, 1),
                'months' => round($weeksToGoal / 4.33, 1),
                'weight_difference' => round($weightDifference, 1)
            ];
        }

        return response()->json([
            'success' => true,
            'patient' => [
                'name' => $patient->full_name,
                'current_weight' => $patient->weight,
                'height' => $patient->height,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'bmi' => $patient->bmi,
                'bmi_category' => $patient->bmi_category
            ],
            'calories' => $calorieData,
            'macronutrients' => $macros,
            'time_to_goal' => $timeToGoal,
            'recommendations' => $this->getCalorieRecommendations($request->goal, $calorieData)
        ]);
    }

    /**
     * Calculate macronutrient distribution based on goal.
     */
    private function calculateMacronutrients(float $targetCalories, string $goal): array
    {
        // Macronutrient ratios based on goal
        $ratios = [
            'weight_loss' => ['protein' => 0.30, 'carbs' => 0.35, 'fat' => 0.35],
            'weight_gain' => ['protein' => 0.25, 'carbs' => 0.45, 'fat' => 0.30],
            'muscle_gain' => ['protein' => 0.35, 'carbs' => 0.40, 'fat' => 0.25],
            'maintenance' => ['protein' => 0.25, 'carbs' => 0.45, 'fat' => 0.30]
        ];

        $ratio = $ratios[$goal] ?? $ratios['maintenance'];

        // Calculate grams (protein: 4 cal/g, carbs: 4 cal/g, fat: 9 cal/g)
        $proteinCalories = $targetCalories * $ratio['protein'];
        $carbsCalories = $targetCalories * $ratio['carbs'];
        $fatCalories = $targetCalories * $ratio['fat'];

        return [
            'protein' => [
                'grams' => round($proteinCalories / 4, 0),
                'calories' => round($proteinCalories, 0),
                'percentage' => round($ratio['protein'] * 100, 0)
            ],
            'carbs' => [
                'grams' => round($carbsCalories / 4, 0),
                'calories' => round($carbsCalories, 0),
                'percentage' => round($ratio['carbs'] * 100, 0)
            ],
            'fat' => [
                'grams' => round($fatCalories / 9, 0),
                'calories' => round($fatCalories, 0),
                'percentage' => round($ratio['fat'] * 100, 0)
            ]
        ];
    }

    /**
     * Get recommendations based on calorie calculation.
     */
    private function getCalorieRecommendations(string $goal, array $calorieData): array
    {
        $recommendations = [];

        // Safety recommendations
        if ($goal === 'weight_loss' && $calorieData['target_calories'] <= 1200) {
            $recommendations[] = 'This is a very low-calorie plan. Consider medical supervision.';
        }

        if ($calorieData['daily_calorie_adjustment'] > 500) {
            $recommendations[] = 'Large calorie deficit detected. Consider a more gradual approach.';
        }

        // Activity recommendations
        $activityAdvice = [
            'sedentary' => 'Consider adding light physical activity to boost metabolism.',
            'light' => 'Good activity level. Try to maintain consistency.',
            'moderate' => 'Excellent activity level for your goals.',
            'active' => 'Very good activity level. Monitor recovery.',
            'very_active' => 'High activity level. Ensure adequate nutrition and rest.'
        ];

        $recommendations[] = $activityAdvice[$calorieData['activity_level']] ?? '';

        return array_filter($recommendations);
    }



    /**
     * Create muscle gain nutrition plan.
     */
    public function createMuscleGain(Request $request)
    {
        $user = Auth::user();

        if (!$user->canCreateNutritionPlans()) {
            abort(403, 'You do not have permission to create nutrition plans.');
        }

        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        $template = $this->getMuscleGainTemplate();

        return view('nutrition.create-muscle-gain', compact('patients', 'template'));
    }

    /**
     * Create diabetic nutrition plan.
     */
    public function createDiabetic(Request $request)
    {
        $user = Auth::user();

        if (!$user->canCreateNutritionPlans()) {
            abort(403, 'You do not have permission to create nutrition plans.');
        }

        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        $template = $this->getDiabeticTemplate();

        return view('nutrition.create-diabetic', compact('patients', 'template'));
    }

    /**
     * Calculate nutritional totals for a nutrition plan.
     */
    private function calculateNutritionalTotals(DietPlan $nutrition): array
    {
        $totals = [
            'calories' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fat' => 0,
            'fiber' => 0,
        ];

        foreach ($nutrition->meals as $meal) {
            foreach ($meal->foods as $mealFood) {
                $food = $mealFood->food;
                $quantity = $mealFood->quantity;

                // Skip if food record doesn't exist (might have been deleted)
                if (!$food) {
                    \Log::warning('Food record not found for meal food', [
                        'meal_food_id' => $mealFood->id,
                        'food_id' => $mealFood->food_id,
                        'food_name' => $mealFood->food_name
                    ]);
                    continue;
                }

                // Calculate based on quantity (assuming nutritional info is per 100g)
                $multiplier = $quantity / 100;

                $totals['calories'] += ($food->calories ?? 0) * $multiplier;
                $totals['protein'] += ($food->protein ?? 0) * $multiplier;
                $totals['carbs'] += ($food->carbohydrates ?? 0) * $multiplier;
                $totals['fat'] += ($food->fat ?? 0) * $multiplier;
                $totals['fiber'] += ($food->fiber ?? 0) * $multiplier;
            }
        }

        return $totals;
    }



    /**
     * Get muscle gain template.
     */
    private function getMuscleGainTemplate(): array
    {
        return [
            'title' => 'Muscle Gain Nutrition Plan',
            'goal' => 'muscle_gain',
            'description' => 'A high-protein nutrition plan designed to support muscle growth and recovery.',
            'target_calories' => 2500,
            'target_protein' => 180,
            'target_carbs' => 300,
            'target_fat' => 80,
            'duration_days' => 60,
            'instructions' => 'Eat protein with every meal. Time carbohydrates around workouts. Stay hydrated and get adequate rest.',
            'restrictions' => 'Limit processed foods and empty calories. Focus on whole foods and lean proteins.',
            'sample_meals' => [
                [
                    'name' => 'Breakfast',
                    'time' => '07:00',
                    'foods' => ['Protein smoothie', 'Whole grain toast', 'Banana']
                ],
                [
                    'name' => 'Pre-workout',
                    'time' => '10:00',
                    'foods' => ['Apple', 'Almonds']
                ],
                [
                    'name' => 'Post-workout',
                    'time' => '12:00',
                    'foods' => ['Protein shake', 'Sweet potato']
                ],
                [
                    'name' => 'Lunch',
                    'time' => '14:00',
                    'foods' => ['Lean beef', 'Brown rice', 'Vegetables']
                ],
                [
                    'name' => 'Dinner',
                    'time' => '19:00',
                    'foods' => ['Salmon', 'Quinoa', 'Broccoli']
                ]
            ]
        ];
    }

    /**
     * Get diabetic template.
     */
    private function getDiabeticTemplate(): array
    {
        return [
            'title' => 'Diabetic Nutrition Plan',
            'goal' => 'diabetic',
            'description' => 'A carefully balanced nutrition plan for managing blood sugar levels and maintaining stable glucose.',
            'target_calories' => 1800,
            'target_protein' => 100,
            'target_carbs' => 180,
            'target_fat' => 60,
            'duration_days' => 90,
            'instructions' => 'Monitor blood sugar regularly. Eat at consistent times. Choose complex carbohydrates over simple sugars.',
            'restrictions' => 'Avoid sugary foods, refined carbohydrates, and high-glycemic foods. Limit saturated fats.',
            'sample_meals' => [
                [
                    'name' => 'Breakfast',
                    'time' => '08:00',
                    'foods' => ['Steel-cut oats', 'Berries', 'Nuts']
                ],
                [
                    'name' => 'Mid-morning',
                    'time' => '10:30',
                    'foods' => ['Apple slices', 'Almond butter']
                ],
                [
                    'name' => 'Lunch',
                    'time' => '13:00',
                    'foods' => ['Grilled chicken', 'Quinoa', 'Green vegetables']
                ],
                [
                    'name' => 'Afternoon snack',
                    'time' => '16:00',
                    'foods' => ['Greek yogurt', 'Cucumber']
                ],
                [
                    'name' => 'Dinner',
                    'time' => '19:00',
                    'foods' => ['Baked fish', 'Sweet potato', 'Spinach salad']
                ]
            ]
        ];
    }

    /**
     * Show weight tracking for a nutrition plan.
     */
    public function weightTracking(DietPlan $dietPlan)
    {
        $user = Auth::user();

        // Check access
        if ($dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to nutrition plan.');
        }

        $dietPlan->load(['patient', 'doctor', 'weightRecords.recorder']);

        // Get weight records ordered by date
        $weightRecords = $dietPlan->weightRecords()->latest('record_date')->get();

        // Calculate weight progress statistics
        $stats = $this->calculateWeightProgressStats($dietPlan);

        return view('nutrition.weight-tracking', compact('dietPlan', 'weightRecords', 'stats'));
    }

    /**
     * Store a new weight record.
     */
    public function storeWeightRecord(Request $request, DietPlan $dietPlan)
    {
        $user = Auth::user();

        // Check access
        if ($dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to nutrition plan.');
        }

        if (!$user->canCreateNutritionPlans()) {
            abort(403, 'You do not have permission to add weight records.');
        }

        $request->validate([
            'weight' => 'required|numeric|min:20|max:500',
            'target_weight' => 'nullable|numeric|min:20|max:500',
            'target_bmi' => 'nullable|numeric|min:10|max:50',
            'height' => 'nullable|numeric|min:100|max:250',
            'record_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'measurements' => 'nullable|array',
            'measurements.waist' => 'nullable|numeric|min:30|max:200',
            'measurements.chest' => 'nullable|numeric|min:50|max:200',
            'measurements.hips' => 'nullable|numeric|min:50|max:200',
            'measurements.arm' => 'nullable|numeric|min:15|max:100',
            'measurements.thigh' => 'nullable|numeric|min:30|max:150',
        ]);

        // Check for duplicate record on same date
        $existingRecord = $dietPlan->weightRecords()
                                  ->where('record_date', $request->record_date)
                                  ->first();

        if ($existingRecord) {
            return back()->withErrors(['record_date' => 'A weight record already exists for this date.']);
        }

        $recordData = [
            'weight' => $request->weight,
            'height' => $request->height ?: $dietPlan->initial_height,
            'record_date' => $request->record_date,
            'notes' => $request->notes,
            'recorded_by' => $user->id,
        ];

        // Add measurements if provided
        if ($request->filled('measurements')) {
            $measurements = array_filter($request->measurements, function($value) {
                return !is_null($value) && $value !== '';
            });

            if (!empty($measurements)) {
                $recordData['measurements'] = $measurements;
            }
        }

        $dietPlan->addWeightRecord($recordData);

        // Update target weight and BMI if provided
        $updateData = [];
        if ($request->filled('target_weight')) {
            $updateData['target_weight'] = $request->target_weight;
        }
        if ($request->filled('target_bmi')) {
            $updateData['target_bmi'] = $request->target_bmi;
        }

        if (!empty($updateData)) {
            $dietPlan->update($updateData);
        }

        return back()->with('success', 'Weight record added successfully.');
    }

    /**
     * Update a weight record.
     */
    public function updateWeightRecord(Request $request, DietPlan $dietPlan, DietPlanWeightRecord $weightRecord)
    {
        $user = Auth::user();

        // Check access
        if ($dietPlan->patient->clinic_id !== $user->clinic_id || $weightRecord->diet_plan_id !== $dietPlan->id) {
            abort(403, 'Unauthorized access.');
        }

        if (!$user->canEditNutritionPlans()) {
            abort(403, 'You do not have permission to edit weight records.');
        }

        $request->validate([
            'weight' => 'required|numeric|min:20|max:500',
            'target_weight' => 'nullable|numeric|min:20|max:500',
            'target_bmi' => 'nullable|numeric|min:10|max:50',
            'height' => 'nullable|numeric|min:100|max:250',
            'record_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'measurements' => 'nullable|array',
        ]);

        // Check for duplicate record on same date (excluding current record)
        $existingRecord = $dietPlan->weightRecords()
                                  ->where('record_date', $request->record_date)
                                  ->where('id', '!=', $weightRecord->id)
                                  ->first();

        if ($existingRecord) {
            return back()->withErrors(['record_date' => 'A weight record already exists for this date.']);
        }

        $updateData = [
            'weight' => $request->weight,
            'height' => $request->height ?: $weightRecord->height,
            'record_date' => $request->record_date,
            'notes' => $request->notes,
        ];

        // Add measurements if provided
        if ($request->filled('measurements')) {
            $measurements = array_filter($request->measurements, function($value) {
                return !is_null($value) && $value !== '';
            });

            $updateData['measurements'] = !empty($measurements) ? $measurements : null;
        }

        $weightRecord->update($updateData);

        // Update target weight and BMI if provided
        $dietPlanUpdateData = [];
        if ($request->filled('target_weight')) {
            $dietPlanUpdateData['target_weight'] = $request->target_weight;
        }
        if ($request->filled('target_bmi')) {
            $dietPlanUpdateData['target_bmi'] = $request->target_bmi;
        }

        if (!empty($dietPlanUpdateData)) {
            $dietPlan->update($dietPlanUpdateData);
        }

        return back()->with('success', 'Weight record updated successfully.');
    }

    /**
     * Delete a weight record.
     */
    public function deleteWeightRecord(DietPlan $dietPlan, DietPlanWeightRecord $weightRecord)
    {
        $user = Auth::user();

        // Check access
        if ($dietPlan->patient->clinic_id !== $user->clinic_id || $weightRecord->diet_plan_id !== $dietPlan->id) {
            abort(403, 'Unauthorized access.');
        }

        if (!$user->canEditNutritionPlans()) {
            abort(403, 'You do not have permission to delete weight records.');
        }

        // Don't allow deletion of initial weight record
        $firstRecord = $dietPlan->weightRecords()->oldest('record_date')->first();
        if ($firstRecord && $firstRecord->id === $weightRecord->id) {
            return back()->withErrors(['error' => 'Cannot delete the initial weight record.']);
        }

        $weightRecord->delete();

        return back()->with('success', 'Weight record deleted successfully.');
    }

    /**
     * Calculate weight progress statistics.
     */
    private function calculateWeightProgressStats(DietPlan $dietPlan): array
    {
        $stats = [
            'total_records' => $dietPlan->weightRecords()->count(),
            'total_weight_change' => $dietPlan->total_weight_change,
            'weight_change_percentage' => $dietPlan->weight_change_percentage,
            'progress_percentage' => $dietPlan->weight_progress_percentage,
            'bmi_change' => $dietPlan->bmi_change,
            'goal_achieved' => $dietPlan->isWeightGoalAchieved(),
            'average_weekly_change' => null,
            'projected_completion' => null,
        ];

        // Calculate average weekly weight change
        $records = $dietPlan->weightRecords()->oldest('record_date')->get();
        if ($records->count() >= 2) {
            $firstRecord = $records->first();
            $lastRecord = $records->last();

            $daysDiff = $firstRecord->record_date->diffInDays($lastRecord->record_date);
            if ($daysDiff > 0) {
                $weeksDiff = $daysDiff / 7;
                $totalChange = $lastRecord->weight - $firstRecord->weight;
                $stats['average_weekly_change'] = $totalChange / $weeksDiff;
            }
        }

        // Project completion date if target weight is set
        if ($dietPlan->target_weight && $dietPlan->current_weight && $stats['average_weekly_change']) {
            $remainingWeight = abs($dietPlan->target_weight - $dietPlan->current_weight);
            $weeklyProgress = abs($stats['average_weekly_change']);

            if ($weeklyProgress > 0) {
                $weeksToGoal = $remainingWeight / $weeklyProgress;
                $stats['projected_completion'] = now()->addWeeks($weeksToGoal);
            }
        }

        return $stats;
    }
}
