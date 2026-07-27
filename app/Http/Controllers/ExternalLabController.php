<?php

namespace App\Http\Controllers;

use App\Models\ExternalLab;
use App\Http\Traits\SmartSearch;
use Illuminate\Http\Request;

class ExternalLabController extends Controller
{
    use SmartSearch;
    /**
     * Display a listing of external labs.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Settings are admin-only: allow only Clinic Admins or Super Admins
        if (!($user->isSuperAdmin() || $user->isClinicAdmin())) {
            abort(403, 'Only administrators can manage external laboratories.');
        }

        $query = ExternalLab::byClinic($user->clinic_id)->byType('medical')->with('creator');

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

        // Apply lab type filter
        if ($request->filled('lab_type')) {
            $query->byType($request->lab_type);
        }

        $externalLabs = $query->ordered()->paginate(15);

        return view('external-labs.index', compact('externalLabs'));
    }

    /**
     * Show the specified external lab (for AJAX requests).
     */
    public function show(ExternalLab $externalLab)
    {
        $user = auth()->user();

        // Ensure lab belongs to user's clinic
        if ($externalLab->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to external laboratory.');
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'lab' => $externalLab
            ]);
        }

        return redirect()->route('external-labs.index');
    }

    /**
     * Store a newly created external lab.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Only admins can create external labs
        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can create external laboratories.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'lab_type' => 'required|in:medical,dental',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            // Dental-specific fields
            'turnaround_days' => 'nullable|integer|min:1',
            'accepts_digital_impressions' => 'nullable|boolean',
            'equipment_capabilities' => 'nullable|string',
        ]);

        $data = [
            'name' => $request->name,
            'lab_type' => $request->lab_type,
            'address' => $request->address,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'email' => $request->email,
            'website' => $request->website,
            'notes' => $request->notes,
            'sort_order' => $request->sort_order ?? 0,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
        ];

        // Add dental-specific fields if lab type is dental
        if ($request->lab_type === 'dental') {
            $data['turnaround_days'] = $request->turnaround_days;
            $data['accepts_digital_impressions'] = $request->has('accepts_digital_impressions');
            $data['equipment_capabilities'] = $request->equipment_capabilities;
        }

        ExternalLab::create($data);

        return back()->with('success', 'External laboratory added successfully.');
    }

    /**
     * Update the specified external lab.
     */
    public function update(Request $request, ExternalLab $externalLab)
    {
        $user = auth()->user();

        // Only admins can update external labs
        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can update external laboratories.');
        }

        // Ensure lab belongs to user's clinic
        if ($externalLab->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to external laboratory.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'lab_type' => 'required|in:medical,dental',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            // Dental-specific fields
            'turnaround_days' => 'nullable|integer|min:1',
            'accepts_digital_impressions' => 'nullable|boolean',
            'equipment_capabilities' => 'nullable|string',
        ]);

        $data = [
            'name' => $request->name,
            'lab_type' => $request->lab_type,
            'address' => $request->address,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'email' => $request->email,
            'website' => $request->website,
            'notes' => $request->notes,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];

        // Add dental-specific fields if lab type is dental
        if ($request->lab_type === 'dental') {
            $data['turnaround_days'] = $request->turnaround_days;
            $data['accepts_digital_impressions'] = $request->has('accepts_digital_impressions');
            $data['equipment_capabilities'] = $request->equipment_capabilities;
        }

        $externalLab->update($data);

        return back()->with('success', 'External laboratory updated successfully.');
    }

    /**
     * Remove the specified external lab.
     */
    public function destroy(ExternalLab $externalLab)
    {
        $user = auth()->user();

        // Only admins can delete external labs
        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can delete external laboratories.');
        }

        // Ensure lab belongs to user's clinic
        if ($externalLab->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to external laboratory.');
        }

        $externalLab->delete();

        return back()->with('success', 'External laboratory deleted successfully.');
    }

    /**
     * Toggle the active status of an external lab.
     */
    public function toggleStatus(ExternalLab $externalLab)
    {
        $user = auth()->user();

        // Only admins can toggle status
        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can change laboratory status.');
        }

        // Ensure lab belongs to user's clinic
        if ($externalLab->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to external laboratory.');
        }

        $externalLab->update([
            'is_active' => !$externalLab->is_active
        ]);

        $status = $externalLab->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "External laboratory {$status} successfully.");
    }
}
