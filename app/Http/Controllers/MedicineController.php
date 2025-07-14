<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicineController extends Controller
{
    /**
     * Display a listing of medicines.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Medicine::with(['creator'])
            ->where('clinic_id', $user->clinic_id);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('brand_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('form')) {
            $query->where('form', $request->form);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('frequent')) {
            $query->where('is_frequent', true);
        }

        $medicines = $query->latest()->paginate(15);

        // Get statistics
        $stats = [
            'total' => Medicine::where('clinic_id', $user->clinic_id)->count(),
            'active' => Medicine::where('clinic_id', $user->clinic_id)->where('is_active', true)->count(),
            'frequent' => Medicine::where('clinic_id', $user->clinic_id)->where('is_frequent', true)->count(),
            'forms' => Medicine::where('clinic_id', $user->clinic_id)->distinct('form')->count('form'),
        ];

        return view('medicines.index', compact('medicines', 'stats'));
    }

    /**
     * Show the form for creating a new medicine.
     */
    public function create()
    {
        return view('medicines.create');
    }

    /**
     * Store a newly created medicine.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'form' => 'required|string|in:' . implode(',', array_keys(Medicine::FORMS)),
            'description' => 'nullable|string|max:1000',
            'side_effects' => 'nullable|string|max:1000',
            'contraindications' => 'nullable|string|max:1000',
            'is_frequent' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate medicine in the same clinic
        $exists = Medicine::where('clinic_id', $user->clinic_id)
            ->where('name', $request->name)
            ->where('dosage', $request->dosage)
            ->where('form', $request->form)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', __('A medicine with the same name, dosage, and form already exists in your inventory.'));
        }

        Medicine::create([
            'name' => $request->name,
            'generic_name' => $request->generic_name,
            'brand_name' => $request->brand_name,
            'dosage' => $request->dosage,
            'form' => $request->form,
            'description' => $request->description,
            'side_effects' => $request->side_effects,
            'contraindications' => $request->contraindications,
            'is_frequent' => $request->boolean('is_frequent'),
            'is_active' => $request->boolean('is_active', true),
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
        ]);

        return redirect()->route('medicines.index')
            ->with('success', __('Medicine added to inventory successfully.'));
    }

    /**
     * Display the specified medicine.
     */
    public function show(Medicine $medicine)
    {
        $this->authorize('view', $medicine);
        
        $medicine->load(['creator', 'prescriptionMedicines.prescription.patient']);
        
        // Get usage statistics
        $usageStats = [
            'total_prescriptions' => $medicine->prescriptionMedicines()->count(),
            'recent_prescriptions' => $medicine->prescriptionMedicines()
                ->with(['prescription.patient'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return view('medicines.show', compact('medicine', 'usageStats'));
    }

    /**
     * Show the form for editing the specified medicine.
     */
    public function edit(Medicine $medicine)
    {
        $this->authorize('update', $medicine);
        
        return view('medicines.edit', compact('medicine'));
    }

    /**
     * Update the specified medicine.
     */
    public function update(Request $request, Medicine $medicine)
    {
        $this->authorize('update', $medicine);

        $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'form' => 'required|string|in:' . implode(',', array_keys(Medicine::FORMS)),
            'description' => 'nullable|string|max:1000',
            'side_effects' => 'nullable|string|max:1000',
            'contraindications' => 'nullable|string|max:1000',
            'is_frequent' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate medicine in the same clinic (excluding current)
        $exists = Medicine::where('clinic_id', $medicine->clinic_id)
            ->where('name', $request->name)
            ->where('dosage', $request->dosage)
            ->where('form', $request->form)
            ->where('id', '!=', $medicine->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', __('A medicine with the same name, dosage, and form already exists in your inventory.'));
        }

        $medicine->update([
            'name' => $request->name,
            'generic_name' => $request->generic_name,
            'brand_name' => $request->brand_name,
            'dosage' => $request->dosage,
            'form' => $request->form,
            'description' => $request->description,
            'side_effects' => $request->side_effects,
            'contraindications' => $request->contraindications,
            'is_frequent' => $request->boolean('is_frequent'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('medicines.show', $medicine)
            ->with('success', __('Medicine updated successfully.'));
    }

    /**
     * Remove the specified medicine from storage.
     */
    public function destroy(Medicine $medicine)
    {
        $this->authorize('delete', $medicine);

        // Check if medicine is used in any prescriptions
        if ($medicine->prescriptionMedicines()->exists()) {
            return back()->with('error', __('Cannot delete medicine that has been used in prescriptions. You can deactivate it instead.'));
        }

        $medicine->delete();

        return redirect()->route('medicines.index')
            ->with('success', __('Medicine deleted successfully.'));
    }

    /**
     * Toggle medicine active status.
     */
    public function toggleStatus(Medicine $medicine)
    {
        $this->authorize('update', $medicine);

        $medicine->update([
            'is_active' => !$medicine->is_active
        ]);

        $status = $medicine->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', __("Medicine {$status} successfully."));
    }

    /**
     * Toggle medicine frequent status.
     */
    public function toggleFrequent(Medicine $medicine)
    {
        $this->authorize('update', $medicine);

        $medicine->update([
            'is_frequent' => !$medicine->is_frequent
        ]);

        $status = $medicine->is_frequent ? 'marked as frequent' : 'removed from frequent';
        
        return back()->with('success', __("Medicine {$status} successfully."));
    }

    /**
     * Get medicines for AJAX requests (for prescription forms).
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q', '');

        $medicines = Medicine::where('clinic_id', $user->clinic_id)
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('generic_name', 'like', "%{$search}%")
                      ->orWhere('brand_name', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'generic_name', 'brand_name', 'dosage', 'form')
            ->limit(20)
            ->get()
            ->map(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'text' => $medicine->full_name,
                    'name' => $medicine->name,
                    'generic_name' => $medicine->generic_name,
                    'brand_name' => $medicine->brand_name,
                    'dosage' => $medicine->dosage,
                    'form' => $medicine->form,
                ];
            });

        return response()->json($medicines);
    }
}
