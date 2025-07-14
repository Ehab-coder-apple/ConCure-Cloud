<?php

namespace App\Http\Controllers;

use App\Models\FoodGroup;
use Illuminate\Http\Request;

class FoodGroupController extends Controller
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
     * Display a listing of food groups.
     */
    public function index(Request $request)
    {
        $this->checkFoodPermission('food_database_view');
        $query = FoodGroup::withCount('foods');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $foodGroups = $query->active()->ordered()->paginate(15);

        return view('food-groups.index', compact('foodGroups'));
    }

    /**
     * Show the form for creating a new food group.
     */
    public function create()
    {
        $this->checkFoodPermission('food_database_groups');
        
        return view('food-groups.create');
    }

    /**
     * Store a newly created food group.
     */
    public function store(Request $request)
    {
        $this->checkFoodPermission('food_database_groups');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
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

        $foodGroup = FoodGroup::create([
            'name' => $request->name,
            'name_translations' => $nameTranslations,
            'description' => $request->description,
            'description_translations' => $descriptionTranslations,
            'color' => $request->color ?? '#6c757d',
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('food-groups.index')
                        ->with('success', 'Food group created successfully.');
    }

    /**
     * Display the specified food group.
     */
    public function show(FoodGroup $foodGroup)
    {
        $foodGroup->load(['foods' => function ($query) {
            $query->active()->orderBy('name');
        }]);
        
        return view('food-groups.show', compact('foodGroup'));
    }

    /**
     * Show the form for editing the specified food group.
     */
    public function edit(FoodGroup $foodGroup)
    {
        $this->checkFoodPermission('food_database_groups');
        
        return view('food-groups.edit', compact('foodGroup'));
    }

    /**
     * Update the specified food group.
     */
    public function update(Request $request, FoodGroup $foodGroup)
    {
        $this->checkFoodPermission('food_database_groups');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Prepare translations
        $nameTranslations = $foodGroup->name_translations ?? [];
        $descriptionTranslations = $foodGroup->description_translations ?? [];
        
        foreach (['en', 'ar', 'ku'] as $locale) {
            if ($request->filled("name_{$locale}")) {
                $nameTranslations[$locale] = $request->input("name_{$locale}");
            }
            if ($request->filled("description_{$locale}")) {
                $descriptionTranslations[$locale] = $request->input("description_{$locale}");
            }
        }

        $foodGroup->update([
            'name' => $request->name,
            'name_translations' => $nameTranslations,
            'description' => $request->description,
            'description_translations' => $descriptionTranslations,
            'color' => $request->color ?? '#6c757d',
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('food-groups.index')
                        ->with('success', 'Food group updated successfully.');
    }

    /**
     * Remove the specified food group.
     */
    public function destroy(FoodGroup $foodGroup)
    {
        $this->checkFoodPermission('food_database_groups');
        
        // Check if food group has any foods
        if ($foodGroup->foods()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete food group that contains foods.']);
        }

        $foodGroup->delete();

        return redirect()->route('food-groups.index')
                        ->with('success', 'Food group deleted successfully.');
    }

    /**
     * Get food groups for AJAX requests.
     */
    public function api(Request $request)
    {
        $foodGroups = FoodGroup::active()
                              ->ordered()
                              ->get()
                              ->map(function ($group) {
                                  return [
                                      'id' => $group->id,
                                      'name' => $group->translated_name,
                                      'color' => $group->color,
                                      'food_count' => $group->food_count,
                                  ];
                              });

        return response()->json($foodGroups);
    }
}
