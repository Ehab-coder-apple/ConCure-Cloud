<?php

namespace App\Http\Controllers;

use App\Models\ExternalLab;
use App\Http\Traits\SmartSearch;
use Illuminate\Http\Request;

class DentalExternalLabController extends Controller
{
    use SmartSearch;

    /**
     * Display a listing of dental external labs.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Settings are admin-only: allow only Clinic Admins or Super Admins
        if (!($user->isSuperAdmin() || $user->isClinicAdmin())) {
            abort(403, 'Only administrators can manage dental laboratories.');
        }

        $query = ExternalLab::byClinic($user->clinic_id)->dental()->with('creator');

        // Apply smart search filter
        $searchTerm = $this->getValidatedSearchTerm($request);
        if ($searchTerm !== null) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $dentalLabs = $query->ordered()->paginate(15);

        return view('dental.external-labs.index', compact('dentalLabs'));
    }

    /**
     * Show the specified dental external lab (for AJAX requests).
     */
    public function show(ExternalLab $dentalLab)
    {
        $user = auth()->user();

        if ($dentalLab->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to dental laboratory.');
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'lab' => $dentalLab
            ]);
        }

        return redirect()->route('dental.external-labs.index');
    }

    /**
     * Store a newly created dental external lab.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can create dental laboratories.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'turnaround_days' => 'nullable|integer|min:1',
            'accepts_digital_impressions' => 'nullable|boolean',
            'equipment_capabilities' => 'nullable|string',
        ]);

        ExternalLab::create([
            'name' => $request->name,
            'lab_type' => 'dental',
            'address' => $request->address,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'email' => $request->email,
            'website' => $request->website,
            'notes' => $request->notes,
            'sort_order' => $request->sort_order ?? 0,
            'turnaround_days' => $request->turnaround_days,
            'accepts_digital_impressions' => $request->has('accepts_digital_impressions'),
            'equipment_capabilities' => $request->equipment_capabilities,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Dental laboratory added successfully.');
    }

    /**
     * Update the specified dental external lab.
     */
    public function update(Request $request, ExternalLab $dentalLab)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can update dental laboratories.');
        }

        if ($dentalLab->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to dental laboratory.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'turnaround_days' => 'nullable|integer|min:1',
            'accepts_digital_impressions' => 'nullable|boolean',
            'equipment_capabilities' => 'nullable|string',
        ]);



        $dentalLab->update([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'email' => $request->email,
            'website' => $request->website,
            'notes' => $request->notes,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active'),
            'turnaround_days' => $request->turnaround_days,
            'accepts_digital_impressions' => $request->has('accepts_digital_impressions'),
            'equipment_capabilities' => $request->equipment_capabilities,
        ]);

        return back()->with('success', 'Dental laboratory updated successfully.');
    }

    /**
     * Remove the specified dental external lab.
     */
    public function destroy(ExternalLab $dentalLab)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can delete dental laboratories.');
        }

        if ($dentalLab->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to dental laboratory.');
        }

        $dentalLab->delete();

        return back()->with('success', 'Dental laboratory deleted successfully.');
    }

    /**
     * Toggle the active status of a dental external lab.
     */
    public function toggleStatus(ExternalLab $dentalLab)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can change dental laboratory status.');
        }

        if ($dentalLab->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to dental laboratory.');
        }

        $dentalLab->update([
            'is_active' => !$dentalLab->is_active
        ]);

        $status = $dentalLab->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Dental laboratory {$status} successfully.");
    }
}