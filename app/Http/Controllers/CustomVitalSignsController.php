<?php

namespace App\Http\Controllers;

use App\Models\CustomVitalSignsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomVitalSignsController extends Controller
{
    /**
     * Display a listing of custom vital signs.
     */
    public function index(Request $request)
    {
        // Handle bulk actions
        if ($request->has('bulk_action') && $request->has('sign_ids')) {
            $this->handleBulkAction($request);
            return redirect()->route('admin.custom-vital-signs.index')
                            ->with('success', 'Bulk action completed successfully.');
        }

        $customSigns = CustomVitalSignsConfig::forClinic(Auth::user()->clinic_id)
                                            ->ordered()
                                            ->get();

        return view('admin.custom-vital-signs.index', compact('customSigns'));
    }

    /**
     * Handle bulk actions on custom vital signs.
     */
    private function handleBulkAction(Request $request)
    {
        $request->validate([
            'bulk_action' => 'required|in:activate,deactivate',
            'sign_ids' => 'required|array',
            'sign_ids.*' => 'exists:custom_vital_signs_config,id',
        ]);

        $signIds = $request->sign_ids;
        $action = $request->bulk_action;

        // Ensure user can only modify their clinic's custom vital signs
        $signs = CustomVitalSignsConfig::whereIn('id', $signIds)
                                      ->where('clinic_id', Auth::user()->clinic_id)
                                      ->get();

        foreach ($signs as $sign) {
            $sign->update([
                'is_active' => $action === 'activate'
            ]);
        }
    }

    /**
     * Show the form for creating a new custom vital sign.
     */
    public function create()
    {
        return view('admin.custom-vital-signs.create');
    }

    /**
     * Store a newly created custom vital sign.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'type' => 'required|in:number,text,select',
            'option_keys' => 'nullable|array',
            'option_values' => 'nullable|array',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric|gte:min_value',
            'normal_range' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'name', 'unit', 'type', 'min_value', 'max_value', 'normal_range', 'sort_order'
        ]);

        $data['clinic_id'] = Auth::user()->clinic_id;
        $data['is_active'] = true;

        // Handle options for select type
        if ($request->type === 'select' && $request->has('option_keys') && $request->has('option_values')) {
            $options = [];
            $keys = $request->option_keys;
            $values = $request->option_values;

            for ($i = 0; $i < count($keys); $i++) {
                if (!empty($keys[$i]) && !empty($values[$i])) {
                    $options[$keys[$i]] = $values[$i];
                }
            }
            $data['options'] = $options;
        } else {
            $data['options'] = null;
        }

        CustomVitalSignsConfig::create($data);

        return redirect()->route('admin.custom-vital-signs.index')
                        ->with('success', 'Custom vital sign created successfully.');
    }

    /**
     * Show the form for editing the specified custom vital sign.
     */
    public function edit(CustomVitalSignsConfig $customVitalSign)
    {
        // Ensure user can only edit their clinic's custom vital signs
        if ($customVitalSign->clinic_id !== Auth::user()->clinic_id) {
            abort(403);
        }

        return view('admin.custom-vital-signs.edit', compact('customVitalSign'));
    }

    /**
     * Update the specified custom vital sign.
     */
    public function update(Request $request, CustomVitalSignsConfig $customVitalSign)
    {
        // Ensure user can only edit their clinic's custom vital signs
        if ($customVitalSign->clinic_id !== Auth::user()->clinic_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'type' => 'required|in:number,text,select',
            'option_keys' => 'nullable|array',
            'option_values' => 'nullable|array',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric|gte:min_value',
            'normal_range' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = $request->only([
            'name', 'unit', 'type', 'min_value', 'max_value', 'normal_range', 'sort_order'
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        // Handle options for select type
        if ($request->type === 'select' && $request->has('option_keys') && $request->has('option_values')) {
            $options = [];
            $keys = $request->option_keys;
            $values = $request->option_values;

            for ($i = 0; $i < count($keys); $i++) {
                if (!empty($keys[$i]) && !empty($values[$i])) {
                    $options[$keys[$i]] = $values[$i];
                }
            }
            $data['options'] = $options;
        } else {
            $data['options'] = null;
        }

        $customVitalSign->update($data);

        return redirect()->route('admin.custom-vital-signs.index')
                        ->with('success', 'Custom vital sign updated successfully.');
    }

    /**
     * Remove the specified custom vital sign.
     */
    public function destroy(CustomVitalSignsConfig $customVitalSign)
    {
        // Ensure user can only delete their clinic's custom vital signs
        if ($customVitalSign->clinic_id !== Auth::user()->clinic_id) {
            abort(403);
        }

        $customVitalSign->delete();

        return redirect()->route('admin.custom-vital-signs.index')
                        ->with('success', 'Custom vital sign deleted successfully.');
    }

    /**
     * Toggle the active status of a custom vital sign.
     */
    public function toggleStatus(CustomVitalSignsConfig $customVitalSign)
    {
        // Ensure user can only toggle their clinic's custom vital signs
        if ($customVitalSign->clinic_id !== Auth::user()->clinic_id) {
            abort(403);
        }

        $customVitalSign->update([
            'is_active' => !$customVitalSign->is_active
        ]);

        $status = $customVitalSign->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Custom vital sign {$status} successfully.");
    }
}
