<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\FoodGroup;
use App\Imports\FoodsImport;
use App\Exports\FoodsTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FoodController extends Controller
{
    /**
     * Check food database permission (disabled in development mode)
     */
    private function checkFoodPermission($permission)
    {
        // DEVELOPMENT MODE: Disable all authorization checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return;
        }

        $user = auth()->user();
        if (!$user || !$user->hasPermission($permission)) {
            abort(403, 'Unauthorized access to food database.');
        }
    }
    /**
     * Display a listing of foods.
     */
    public function index(Request $request)
    {
        // Check food database view permission
        $this->checkFoodPermission('food_database_view');

        $user = auth()->user();
        $query = Food::with(['foodGroup', 'clinic', 'creator']);

        // Filter by clinic - only show foods for current clinic
        if ($user && $user->clinic_id) {
            $query->byClinic($user->clinic_id);
        } elseif ($user) {
            // If user has no clinic_id, show foods with null clinic_id created by this user
            $query->where(function($q) use ($user) {
                $q->whereNull('clinic_id')
                  ->where('created_by', $user->id);
            });
        }

        // Apply filters
        if ($request->filled('food_group_id')) {
            $query->byFoodGroup($request->food_group_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            if ($request->type === 'custom') {
                $query->custom();
            } elseif ($request->type === 'standard') {
                $query->standard();
            }
        }

        if ($request->filled('nutrition_filter')) {
            switch ($request->nutrition_filter) {
                case 'high_protein':
                    $query->highProtein();
                    break;
                case 'low_calorie':
                    $query->lowCalorie();
                    break;
                case 'high_fiber':
                    $query->highFiber();
                    break;
            }
        }

        $foods = $query->active()->orderBy('name')->paginate(20);
        $foodGroups = FoodGroup::active()->ordered()->get();

        return view('foods.index', compact('foods', 'foodGroups'));
    }

    /**
     * Show the form for creating a new food.
     */
    public function create()
    {
        $this->checkFoodPermission('food_database_create');
        
        $foodGroups = FoodGroup::active()->ordered()->get();
        
        return view('foods.create', compact('foodGroups'));
    }

    /**
     * Store a newly created food.
     */
    public function store(Request $request)
    {
        $this->checkFoodPermission('food_database_create');

        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'food_group_id' => 'required|exists:food_groups,id',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'calories' => 'required|numeric|min:0|max:9999',
            'protein' => 'required|numeric|min:0|max:999',
            'carbohydrates' => 'required|numeric|min:0|max:999',
            'fat' => 'required|numeric|min:0|max:999',
            'fiber' => 'nullable|numeric|min:0|max:999',
            'sugar' => 'nullable|numeric|min:0|max:999',
            'sodium' => 'nullable|numeric|min:0|max:99999',
            'potassium' => 'nullable|numeric|min:0|max:99999',
            'calcium' => 'nullable|numeric|min:0|max:99999',
            'iron' => 'nullable|numeric|min:0|max:999',
            'vitamin_c' => 'nullable|numeric|min:0|max:999',
            'vitamin_a' => 'nullable|numeric|min:0|max:99999',
            'serving_size' => 'nullable|string|max:255',
            'serving_weight' => 'nullable|numeric|min:0|max:9999',
        ]);

        // Prepare translations
        $nameTranslations = [];
        $descriptionTranslations = [];
        
        foreach (['en', 'ar', 'ku'] as $locale) {
            if ($request->filled("name_{$locale}")) {
                $nameTranslations[$locale] = $request->input("name_{$locale}");
            }
            if ($request->filled("description_{$locale}")) {
                $descriptionTranslations[$locale] = $request->input("description_{$locale}");
            }
        }

        $food = Food::create([
            'name' => $request->name,
            'name_translations' => $nameTranslations,
            'food_group_id' => $request->food_group_id,
            'description' => $request->description,
            'description_translations' => $descriptionTranslations,
            'calories' => $request->calories,
            'protein' => $request->protein,
            'carbohydrates' => $request->carbohydrates,
            'fat' => $request->fat,
            'fiber' => $request->fiber ?? 0,
            'sugar' => $request->sugar ?? 0,
            'sodium' => $request->sodium ?? 0,
            'potassium' => $request->potassium ?? 0,
            'calcium' => $request->calcium ?? 0,
            'iron' => $request->iron ?? 0,
            'vitamin_c' => $request->vitamin_c ?? 0,
            'vitamin_a' => $request->vitamin_a ?? 0,
            'serving_size' => $request->serving_size,
            'serving_weight' => $request->serving_weight,
            'is_custom' => true,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'is_active' => true,
        ]);

        return redirect()->route('foods.show', $food)
                        ->with('success', 'Food item created successfully.');
    }

    /**
     * Display the specified food.
     */
    public function show(Food $food)
    {
        $food->load(['foodGroup', 'clinic', 'creator']);
        
        return view('foods.show', compact('food'));
    }

    /**
     * Show the form for editing the specified food.
     */
    public function edit(Food $food)
    {
        $this->checkFoodPermission('food_database_edit');

        // Only allow editing custom foods
        if ($food->is_custom === false) {
            abort(403, 'Cannot edit standard food items.');
        }
        
        $foodGroups = FoodGroup::active()->ordered()->get();
        
        return view('foods.edit', compact('food', 'foodGroups'));
    }

    /**
     * Update the specified food.
     */
    public function update(Request $request, Food $food)
    {
        $this->checkFoodPermission('food_database_edit');

        // Only allow editing custom foods
        if ($food->is_custom === false) {
            abort(403, 'Cannot edit standard food items.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'food_group_id' => 'required|exists:food_groups,id',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'calories' => 'required|numeric|min:0|max:9999',
            'protein' => 'required|numeric|min:0|max:999',
            'carbohydrates' => 'required|numeric|min:0|max:999',
            'fat' => 'required|numeric|min:0|max:999',
            'fiber' => 'nullable|numeric|min:0|max:999',
            'sugar' => 'nullable|numeric|min:0|max:999',
            'sodium' => 'nullable|numeric|min:0|max:99999',
            'potassium' => 'nullable|numeric|min:0|max:99999',
            'calcium' => 'nullable|numeric|min:0|max:99999',
            'iron' => 'nullable|numeric|min:0|max:999',
            'vitamin_c' => 'nullable|numeric|min:0|max:999',
            'vitamin_a' => 'nullable|numeric|min:0|max:99999',
            'serving_size' => 'nullable|string|max:255',
            'serving_weight' => 'nullable|numeric|min:0|max:9999',
            'is_active' => 'boolean',
        ]);

        // Prepare translations
        $nameTranslations = $food->name_translations ?? [];
        $descriptionTranslations = $food->description_translations ?? [];
        
        foreach (['en', 'ar', 'ku'] as $locale) {
            if ($request->filled("name_{$locale}")) {
                $nameTranslations[$locale] = $request->input("name_{$locale}");
            }
            if ($request->filled("description_{$locale}")) {
                $descriptionTranslations[$locale] = $request->input("description_{$locale}");
            }
        }

        $food->update([
            'name' => $request->name,
            'name_translations' => $nameTranslations,
            'food_group_id' => $request->food_group_id,
            'description' => $request->description,
            'description_translations' => $descriptionTranslations,
            'calories' => $request->calories,
            'protein' => $request->protein,
            'carbohydrates' => $request->carbohydrates,
            'fat' => $request->fat,
            'fiber' => $request->fiber ?? 0,
            'sugar' => $request->sugar ?? 0,
            'sodium' => $request->sodium ?? 0,
            'potassium' => $request->potassium ?? 0,
            'calcium' => $request->calcium ?? 0,
            'iron' => $request->iron ?? 0,
            'vitamin_c' => $request->vitamin_c ?? 0,
            'vitamin_a' => $request->vitamin_a ?? 0,
            'serving_size' => $request->serving_size,
            'serving_weight' => $request->serving_weight,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('foods.show', $food)
                        ->with('success', 'Food item updated successfully.');
    }

    /**
     * Remove the specified food.
     */
    public function destroy(Food $food)
    {
        $this->checkFoodPermission('food_database_delete');

        // Only allow deleting custom foods
        if ($food->is_custom === false) {
            abort(403, 'Cannot delete standard food items.');
        }

        // Check if food is used in any diet plans
        if ($food->dietPlanMealFoods()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete food item that is used in diet plans.']);
        }

        $food->delete();

        return redirect()->route('foods.index')
                        ->with('success', 'Food item deleted successfully.');
    }

    /**
     * Clear all foods from the database.
     */
    public function clearAll()
    {
        $this->checkFoodPermission('food_database_clear');

        $user = auth()->user();

        if (!$user || !$user->clinic_id) {
            return redirect()->route('foods.index')
                            ->with('error', 'Unable to determine clinic. Please try logging in again.');
        }

        // Get count for confirmation message
        $totalCount = Food::where('clinic_id', $user->clinic_id)->count();

        // Also count null clinic_id foods created by this user
        if (!$user->clinic_id) {
            $totalCount = Food::whereNull('clinic_id')->where('created_by', $user->id)->count();
        } else {
            // Add null clinic_id foods created by this user
            $totalCount += Food::whereNull('clinic_id')->where('created_by', $user->id)->count();
        }

        if ($totalCount === 0) {
            return redirect()->route('foods.index')
                            ->with('info', 'No foods to clear.');
        }

        try {
            // Delete all foods for this clinic (including null clinic_id foods if user has no clinic)
            $query = Food::where('clinic_id', $user->clinic_id);

            // If user has no clinic_id, also delete foods with null clinic_id
            if (!$user->clinic_id) {
                $query = Food::whereNull('clinic_id');
            }

            $deletedCount = $query->delete();

            // Also delete any foods with null clinic_id that might belong to this user
            if ($user->clinic_id) {
                $nullFoodsDeleted = Food::whereNull('clinic_id')
                    ->where('created_by', $user->id)
                    ->delete();
                $deletedCount += $nullFoodsDeleted;
            }

            // Verify deletion
            $remainingCount = Food::where('clinic_id', $user->clinic_id)->count();
            if (!$user->clinic_id) {
                $remainingCount = Food::whereNull('clinic_id')->count();
            }

            if ($remainingCount === 0) {
                return redirect()->route('foods.index')
                                ->with('success', "Successfully cleared {$deletedCount} food items. You can now upload a new food list.");
            } else {
                return redirect()->route('foods.index')
                                ->with('warning', "Cleared {$deletedCount} food items, but {$remainingCount} items remain. Some foods may be protected from deletion.");
            }
        } catch (\Exception $e) {
            \Log::error('Failed to clear foods: ' . $e->getMessage());
            return redirect()->route('foods.index')
                            ->with('error', 'Failed to clear foods: ' . $e->getMessage());
        }
    }

    /**
     * Calculate nutrition for a specific quantity.
     */
    public function calculateNutrition(Request $request, Food $food)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'required|string|in:g,kg,mg,cup,tbsp,tsp,serving',
        ]);

        $nutrition = $food->calculateNutrition($request->quantity, $request->unit);

        return response()->json([
            'success' => true,
            'nutrition' => $nutrition,
            'food' => [
                'name' => $food->translated_name,
                'quantity' => $request->quantity,
                'unit' => $request->unit,
            ]
        ]);
    }

    /**
     * Search foods for AJAX requests.
     */
    public function search(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|min:2',
            'q' => 'nullable|string|min:2', // Support both parameters
            'food_group_id' => 'nullable|integer|exists:food_groups,id',
            'language' => 'nullable|string|in:default,en,ar,ku_bahdini,ku_sorani,ku',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Food::with('foodGroup')->active();

        // Handle search term (support both 'search' and 'q' parameters)
        $searchTerm = $request->search ?? $request->q;
        if ($searchTerm && strlen($searchTerm) >= 2) {
            $query->search($searchTerm);
        }
        // If no search term provided, we'll return the first foods (popular/recent foods)

        // Handle food group filter
        if ($request->food_group_id) {
            $query->byFoodGroup($request->food_group_id);
        }

        $foods = $query->orderBy('name')
                      ->limit($request->limit ?? 20)
                      ->get()
                      ->map(function ($food) {
                          return [
                              'id' => $food->id,
                              'name' => $food->name, // Original name
                              'translated_name' => $food->translated_name, // Current locale name
                              'name_translations' => $food->name_translations, // All translations
                              'group' => $food->foodGroup ? $food->foodGroup->translated_name : '',
                              'calories' => $food->calories,
                              'protein' => $food->protein,
                              'carbohydrates' => $food->carbohydrates,
                              'fat' => $food->fat,
                              'serving_size' => $food->serving_size ?? '100g',
                              'serving_weight' => $food->serving_weight ?? 100.0,
                          ];
                      });

        return response()->json(['foods' => $foods]);
    }

    /**
     * Show the import form.
     */
    public function showImport()
    {
        $this->checkFoodPermission('food_database_import');

        return view('foods.import');
    }

    /**
     * Download the import template - Excel is the primary format.
     */
    public function downloadTemplate(Request $request)
    {
        $this->checkFoodPermission('food_database_import');

        $includeSampleData = $request->boolean('sample', true);
        $format = $request->get('format', 'xlsx'); // Default to Excel

        // Only use CSV if explicitly requested
        if ($format === 'csv') {
            return $this->downloadCsvTemplate($includeSampleData);
        }

        // Excel generation with enhanced error handling
        try {
            // Clear any output buffers that might interfere
            while (ob_get_level()) {
                ob_end_clean();
            }

            $filename = 'foods_import_template_' . date('Y-m-d') . '.xlsx';

            // Create and validate the export instance
            $export = new FoodsTemplateExport($includeSampleData);

            // Pre-validate the export data
            $headers = $export->headings();
            $data = $export->array();

            if (empty($headers)) {
                throw new \Exception('Template headers are missing');
            }

            if (!$includeSampleData && empty($data)) {
                throw new \Exception('Empty template data is invalid');
            }

            // Generate Excel file with proper headers and settings
            return Excel::download(
                $export,
                $filename,
                \Maatwebsite\Excel\Excel::XLSX,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]
            );

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Excel template generation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Only fallback to CSV if Excel completely fails
            \Log::info('Falling back to CSV template due to Excel generation failure');

            return response()->json([
                'error' => 'Excel template generation failed. Please try the CSV format or contact support.',
                'fallback_url' => route('foods.import.template', ['sample' => $includeSampleData, 'format' => 'csv'])
            ], 500);
        }
    }

    /**
     * Download CSV template as fallback
     */
    private function downloadCsvTemplate($includeSampleData = true)
    {
        $headers = array_keys(FoodsImport::getExpectedHeaders());

        $filename = 'foods_import_template_' . date('Y-m-d') . '.csv';

        $callback = function() use ($headers, $includeSampleData) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, $headers);

            if ($includeSampleData) {
                // Add sample data
                $sampleData = FoodsImport::getSampleData();
                foreach ($sampleData as $row) {
                    $csvRow = [];
                    foreach ($headers as $header) {
                        $csvRow[] = $row[$header] ?? '';
                    }
                    fputcsv($file, $csvRow);
                }
            } else {
                // Add 3 empty rows for template
                for ($i = 0; $i < 3; $i++) {
                    fputcsv($file, array_fill(0, count($headers), ''));
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import foods from uploaded file.
     */
    public function import(Request $request)
    {
        $this->checkFoodPermission('food_database_import');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new FoodsImport();

            Excel::import($import, $request->file('file'));

            $message = "Import completed successfully! ";
            $message .= "Imported: {$import->getImportedCount()} foods. ";

            if ($import->getSkippedCount() > 0) {
                $message .= "Skipped: {$import->getSkippedCount()} foods (duplicates or errors).";
            }

            if ($import->hasErrors()) {
                $errorMessage = "Some foods could not be imported:\n" . implode("\n", array_slice($import->getErrors(), 0, 10));
                if (count($import->getErrors()) > 10) {
                    $errorMessage .= "\n... and " . (count($import->getErrors()) - 10) . " more errors.";
                }

                return redirect()->route('foods.import')
                    ->with('warning', $message)
                    ->with('import_errors', $errorMessage);
            }

            return redirect()->route('foods.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('foods.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
