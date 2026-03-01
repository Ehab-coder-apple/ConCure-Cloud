<?php

namespace App\Http\Controllers;

use App\Models\DentalLabRequest;
use App\Models\DentalTreatment;
use App\Models\ExternalLab;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DentalLabRequestController extends Controller
{
    /**
	 * Enforce that assigned requests are only visible to the assigned technician/designer and admin-like roles.
     */
    protected function enforceAssignedVisibility(User $user, DentalLabRequest $labRequest): void
    {
        // Admin-like roles can see everything.
        if ($user->isSuperAdmin() || $user->isMasterAdmin() || in_array($user->role, ['admin', 'program_owner'])) {
            return;
        }

	    // Assigned technician can only access requests assigned to them.
	    if ($user->role === 'dental_technician') {
	        if ((int) $labRequest->assigned_technician_id !== (int) $user->id) {
	            abort(403, 'Unauthorized access to lab request.');
	        }
	        return;
	    }

	    // Assigned designer can only access requests assigned to them.
	    if ($user->role === 'cad_cam_designer') {
	        if ((int) $labRequest->assigned_designer_id !== (int) $user->id) {
	            abort(403, 'Unauthorized access to lab request.');
	        }
	        return;
	    }

	    // Other roles cannot access individually assigned requests.
	    if (!is_null($labRequest->assigned_technician_id) || !is_null($labRequest->assigned_designer_id)) {
	        abort(403, 'Unauthorized access to lab request.');
	    }
    }

    /**
     * Display a listing of dental lab requests.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

	    $query = DentalLabRequest::with([
	        'patient',
	        'externalLab',
	        'doctor',
	        'dentalTreatment',
	        'assignedTechnician',
	        'assignedDesigner',
	    ]);

        // Filter by clinic
        if ($user->clinic_id) {
            $query->byClinic($user->clinic_id);
        }

        // Filter by doctor if user is a doctor or dental_dept
        if ($user->role === 'doctor' || $user->role === 'dental_dept') {
            $query->byDoctor($user->id);
        }

        // Apply assignment visibility rules (assigned requests only visible to assigned technician + admins)
        $query->visibleTo($user);

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'uploaded') {
                // Filter for requests with uploaded results
                $query->whereNotNull('result_file_path');
            } else {
                $query->byStatus($request->status);
            }
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('patient_id')) {
            $query->byPatient($request->patient_id);
        }

        if ($request->filled('external_lab_id')) {
            $query->where('external_lab_id', $request->external_lab_id);
        }

        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by doctor name (internal + external)
        if ($request->filled('doctor_name')) {
            $doctorName = $request->doctor_name;
            $query->where(function ($q) use ($doctorName) {
                $q->whereHas('doctor', function ($q2) use ($doctorName) {
                    $q2->where('first_name', 'like', "%{$doctorName}%")
                       ->orWhere('last_name', 'like', "%{$doctorName}%");
                })->orWhere('external_doctor_name', 'like', "%{$doctorName}%");
            });
        }

        // Filter by material
        if ($request->filled('material')) {
            $query->where('material', $request->material);
        }

        // Filter by assigned person (technician + designer)
        if ($request->filled('assigned_person')) {
            $assignedPerson = $request->assigned_person;
            $query->where(function ($q) use ($assignedPerson) {
                $q->whereHas('assignedTechnician', function ($q2) use ($assignedPerson) {
                    $q2->where('first_name', 'like', "%{$assignedPerson}%")
                       ->orWhere('last_name', 'like', "%{$assignedPerson}%");
                })->orWhereHas('assignedDesigner', function ($q2) use ($assignedPerson) {
                    $q2->where('first_name', 'like', "%{$assignedPerson}%")
                       ->orWhere('last_name', 'like', "%{$assignedPerson}%");
                });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'requested_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $requests = $query->paginate(20);

        // Get filter options
        $patients = Patient::query()
                          ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        $dentalLabs = ExternalLab::dental()
                                 ->active()
                                 ->when($user->clinic_id, fn($q) => $q->byClinic($user->clinic_id))
                                 ->ordered()
                                 ->get();

        return view('dental.lab-requests.index', compact('requests', 'patients', 'dentalLabs'));
    }

    /**
     * Show the form for creating a new dental lab request.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can create lab requests.');
        }


        // Get patients
        $patients = Patient::query()
                          ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();


        // Get doctors (include dentists)
        $doctors = User::whereIn('role', ['doctor', 'dental_dept'])
                      ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                      ->where('is_active', true)
                      ->orderBy('first_name')
                      ->get();

	    // Get technicians (Dental Technician)
	    $technicians = User::where('role', 'dental_technician')
	                      ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
	                      ->where('is_active', true)
	                      ->orderBy('first_name')
	                      ->get();

	    // Get designers (CAD/CAM Designer)
	    $designers = User::where('role', 'cad_cam_designer')
	                    ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
	                    ->where('is_active', true)
	                    ->orderBy('first_name')
	                    ->get();

        // Get dental labs
        $dentalLabs = ExternalLab::dental()
                                 ->active()
                                 ->when($user->clinic_id, fn($q) => $q->byClinic($user->clinic_id))
                                 ->ordered()
                                 ->get();

        // Get all dental treatments for the clinic (or filtered by patient if provided)
        $treatments = DentalTreatment::query()
                                    ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                                    ->when($request->filled('patient_id'), fn($q) => $q->where('patient_id', $request->patient_id))
                                    ->whereIn('status', ['planned', 'in_progress', 'completed'])
                                    ->orderBy('created_at', 'desc')
                                    ->get();

	    return view('dental.lab-requests.create', compact(
	        'patients',
	        'treatments',
	        'doctors',
	        'technicians',
	        'designers',
	        'dentalLabs'
	    ));
    }

    /**
     * Store a newly created dental lab request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can create lab requests.');
        }

	        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id', // Phase 35
            'external_patient_name' => 'nullable|string|max:255',
            'dental_treatment_id' => 'nullable|exists:dental_treatments,id',
            'doctor_id' => 'nullable|exists:users,id',
            'external_doctor_name' => 'nullable|string|max:255',
            'assigned_technician_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($q) use ($user) {
	                    $q->where('role', 'dental_technician')
                      ->where('is_active', true);

                    // If the current user belongs to a clinic, only allow assigning within that clinic.
                    if ($user->clinic_id) {
                        $q->where('clinic_id', $user->clinic_id);
                    }
                }),
            ],
	            'assigned_designer_id' => [
	                'nullable',
	                Rule::exists('users', 'id')->where(function ($q) use ($user) {
	                    $q->where('role', 'cad_cam_designer')
	                      ->where('is_active', true);

	                    // If the current user belongs to a clinic, only allow assigning within that clinic.
	                    if ($user->clinic_id) {
	                        $q->where('clinic_id', $user->clinic_id);
	                    }
	                }),
	            ],
            'external_lab_id' => 'nullable|exists:external_labs,id',
            'work_type' => 'required|in:crown,bridge,denture_full,denture_partial,implant_crown,implant_bridge,veneer,inlay_onlay,orthodontic_appliance,night_guard,sports_guard,temporary_crown,other',
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable|array',
	            'quantity' => 'nullable|integer|min:1',
            'shade' => 'nullable|string|max:50',
            'material' => 'nullable|in:porcelain,zirconia,emax,metal,pfm,acrylic,composite,gold,other',
            'specifications' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'requested_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:requested_date',
            'priority' => 'required|in:normal,urgent,rush',
            'estimated_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'prescription_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'impression_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,stl,obj|max:10240',
            'notes' => 'nullable|string',
        ]);

        // Require at least one: patient_id or external_patient_name
        if (empty($validated['patient_id']) && empty($validated['external_patient_name'])) {
            return back()->withInput()->withErrors([
                'patient_id' => __('Please select a registered patient or enter an external patient name.'),
            ]);
        }

        // Clear the other patient field when one is chosen
        if (!empty($validated['patient_id'])) {
            $validated['external_patient_name'] = null;
        } else {
            $validated['patient_id'] = null;
        }

        // Require at least one: doctor_id or external_doctor_name
        if (empty($validated['doctor_id']) && empty($validated['external_doctor_name'])) {
            return back()->withInput()->withErrors([
                'doctor_id' => __('Please select a clinic doctor or enter an external doctor name.'),
            ]);
        }

        // Clear the other doctor field when one is chosen
        if (!empty($validated['doctor_id'])) {
            $validated['external_doctor_name'] = null;
        } else {
            $validated['doctor_id'] = null;
        }

        try {
            // Handle file uploads
            if ($request->hasFile('prescription_file')) {
                $validated['prescription_file_path'] = $request->file('prescription_file')
                    ->store('dental-lab-requests/prescriptions', 'public');
            }

            if ($request->hasFile('impression_file')) {
                $validated['impression_file_path'] = $request->file('impression_file')
                    ->store('dental-lab-requests/impressions', 'public');
            }

            $validated['clinic_id'] = $user->clinic_id;
            $validated['status'] = 'pending';
            $validated['currency'] = $validated['currency'] ?? 'USD';

            $labRequest = DentalLabRequest::create($validated);

            return redirect()
                ->route('dental.lab-requests.show', $labRequest)
                ->with('success', 'Dental lab request created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating dental lab request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified dental lab request.
     */
    public function show(DentalLabRequest $labRequest)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $labRequest->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to lab request.');
            }
        }

        $this->enforceAssignedVisibility($user, $labRequest);

	        $labRequest->load([
            'patient',
            'dentalTreatment',
            'doctor',
            'externalLab',
	            'receivedBy',
	            'assignedTechnician',
	            'assignedDesigner',
        ]);

        return view('dental.lab-requests.show', compact('labRequest'));
    }

    /**
     * Show the form for editing the specified dental lab request.
     */
    public function edit(DentalLabRequest $labRequest)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $labRequest->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to edit lab request.');
            }
        }

        $this->enforceAssignedVisibility($user, $labRequest);

		if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept', 'dental_technician', 'cad_cam_designer'])) {
			abort(403, 'You are not authorized to edit this lab request.');
		}

        // Get patients
        $patients = Patient::query()
                          ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        // Get doctors (include dentists)
        $doctors = User::whereIn('role', ['doctor', 'dental_dept'])
                      ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                      ->where('is_active', true)
                      ->orderBy('first_name')
                      ->get();

	        // Get technicians (Dental Technician)
	        $technicians = User::where('role', 'dental_technician')
	                          ->when($labRequest->clinic_id, fn($q) => $q->where('clinic_id', $labRequest->clinic_id))
	                          ->where('is_active', true)
	                          ->orderBy('first_name')
	                          ->get();

	        // Get designers (CAD/CAM Designer)
	        $designers = User::where('role', 'cad_cam_designer')
	                        ->when($labRequest->clinic_id, fn($q) => $q->where('clinic_id', $labRequest->clinic_id))
	                        ->where('is_active', true)
	                        ->orderBy('first_name')
	                        ->get();

        // Get dental labs
        $dentalLabs = ExternalLab::dental()
                                 ->active()
	                                 ->when($labRequest->clinic_id, fn($q) => $q->byClinic($labRequest->clinic_id))
                                 ->ordered()
                                 ->get();

        // Get dental treatments for the clinic
	        $treatments = DentalTreatment::query()
	                                    ->when($labRequest->clinic_id, fn($q) => $q->where('clinic_id', $labRequest->clinic_id))
                                    ->whereIn('status', ['planned', 'in_progress', 'completed'])
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        // Get users for received_by field
	        $users = User::query()
	                    ->when($labRequest->clinic_id, fn($q) => $q->where('clinic_id', $labRequest->clinic_id))
                    ->where('is_active', true)
                    ->orderBy('first_name')
                    ->get();

	        return view('dental.lab-requests.edit', compact(
            'labRequest',
            'patients',
            'treatments',
            'doctors',
            'technicians',
	            'designers',
            'dentalLabs',
            'users'
        ));
    }

    /**
     * Update the specified dental lab request.
     */
    public function update(Request $request, DentalLabRequest $labRequest)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $labRequest->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to update lab request.');
            }
        }

        $this->enforceAssignedVisibility($user, $labRequest);

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'lab_dept', 'dental_dept', 'dental_technician', 'cad_cam_designer'])) {
            abort(403, 'Only doctors, dental assistants, dentists, and lab staff can update lab requests.');
        }

	        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'external_patient_name' => 'nullable|string|max:255',
            'dental_treatment_id' => 'nullable|exists:dental_treatments,id',
            'doctor_id' => 'nullable|exists:users,id',
            'external_doctor_name' => 'nullable|string|max:255',
            'assigned_technician_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($q) use ($labRequest) {
	                    $q->where('role', 'dental_technician')
                      ->where('is_active', true)
                      ->where('clinic_id', $labRequest->clinic_id);
                }),
            ],
	            'assigned_designer_id' => [
	                'nullable',
	                Rule::exists('users', 'id')->where(function ($q) use ($labRequest) {
	                    $q->where('role', 'cad_cam_designer')
	                      ->where('is_active', true)
	                      ->where('clinic_id', $labRequest->clinic_id);
	                }),
	            ],
            'external_lab_id' => 'nullable|exists:external_labs,id',
            'work_type' => 'required|in:crown,bridge,denture_full,denture_partial,implant_crown,implant_bridge,veneer,inlay_onlay,orthodontic_appliance,night_guard,sports_guard,temporary_crown,other',
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable|array',
	            'quantity' => 'nullable|integer|min:1',
            'shade' => 'nullable|string|max:50',
            'material' => 'nullable|in:porcelain,zirconia,emax,metal,pfm,acrylic,composite,gold,other',
            'specifications' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'requested_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:requested_date',
            'received_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:normal,urgent,rush',
            'communication_method' => 'nullable|in:email,whatsapp,phone,manual',
            'communication_notes' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'prescription_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'impression_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,stl,obj|max:10240',
            'result_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'received_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'quality_notes' => 'nullable|string',
        ]);

        // Require at least one: patient_id or external_patient_name
        if (empty($validated['patient_id']) && empty($validated['external_patient_name'])) {
            return back()->withInput()->withErrors([
                'patient_id' => __('Please select a registered patient or enter an external patient name.'),
            ]);
        }

        // Clear the other patient field when one is chosen
        if (!empty($validated['patient_id'])) {
            $validated['external_patient_name'] = null;
        } else {
            $validated['patient_id'] = null;
        }

        // Require at least one: doctor_id or external_doctor_name
        if (empty($validated['doctor_id']) && empty($validated['external_doctor_name'])) {
            return back()->withInput()->withErrors([
                'doctor_id' => __('Please select a clinic doctor or enter an external doctor name.'),
            ]);
        }

        // Clear the other doctor field when one is chosen
        if (!empty($validated['doctor_id'])) {
            $validated['external_doctor_name'] = null;
        } else {
            $validated['doctor_id'] = null;
        }

        try {
            // Handle file uploads
            if ($request->hasFile('prescription_file')) {
                // Delete old file
                if ($labRequest->prescription_file_path) {
                    Storage::disk('public')->delete($labRequest->prescription_file_path);
                }
                $validated['prescription_file_path'] = $request->file('prescription_file')
                    ->store('dental-lab-requests/prescriptions', 'public');
            }

            if ($request->hasFile('impression_file')) {
                // Delete old file
                if ($labRequest->impression_file_path) {
                    Storage::disk('public')->delete($labRequest->impression_file_path);
                }
                $validated['impression_file_path'] = $request->file('impression_file')
                    ->store('dental-lab-requests/impressions', 'public');
            }

            if ($request->hasFile('result_file')) {
                // Delete old file
                if ($labRequest->result_file_path) {
                    Storage::disk('public')->delete($labRequest->result_file_path);
                }
                $validated['result_file_path'] = $request->file('result_file')
                    ->store('dental-lab-requests/results', 'public');
            }

            // Mark as sent if external lab is selected and not already sent
            if ($request->filled('external_lab_id') && !$labRequest->sent_at) {
                $validated['sent_at'] = now();
            }

            $labRequest->update($validated);

            return redirect()
                ->route('dental.lab-requests.show', $labRequest)
                ->with('success', 'Dental lab request updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error updating dental lab request: ' . $e->getMessage());
        }
    }

    /**
     * Mark a dental lab request as completed (quick action).
     */
    public function markAsCompleted(DentalLabRequest $labRequest)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin() && !$user->isMasterAdmin()) {
            if ($user->clinic_id && $labRequest->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to update lab request.');
            }
        }

        $this->enforceAssignedVisibility($user, $labRequest);

        // Only lab staff (and admins) should be able to mark it completed
        if (!in_array($user->role, ['lab_dept', 'dental_technician', 'cad_cam_designer', 'admin', 'master_admin', 'super_admin'])) {
            abort(403, 'Only lab staff can mark lab requests as completed.');
        }

        if ($labRequest->status === 'completed') {
            return back()->with('success', 'Lab request is already completed.');
        }

        if ($labRequest->status === 'cancelled') {
            return back()->with('error', 'Cannot complete a cancelled lab request.');
        }

        $labRequest->status = 'completed';

        // Track completion date/user if not already set
        if (!$labRequest->received_date) {
            $labRequest->received_date = now()->toDateString();
        }
        if (!$labRequest->received_by) {
            $labRequest->received_by = $user->id;
        }

        $labRequest->save();

        return back()->with('success', 'Lab request marked as completed.');
    }

    /**
     * Remove the specified dental lab request.
     */
    public function destroy(DentalLabRequest $labRequest)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $labRequest->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to delete lab request.');
            }
        }

        $this->enforceAssignedVisibility($user, $labRequest);

        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can delete lab requests.');
        }

        try {
            // Delete files
            if ($labRequest->prescription_file_path) {
                Storage::disk('public')->delete($labRequest->prescription_file_path);
            }
            if ($labRequest->impression_file_path) {
                Storage::disk('public')->delete($labRequest->impression_file_path);
            }
            if ($labRequest->result_file_path) {
                Storage::disk('public')->delete($labRequest->result_file_path);
            }

            $labRequest->delete();

            return redirect()
                ->route('dental.lab-requests.index')
                ->with('success', 'Dental lab request deleted successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error deleting dental lab request: ' . $e->getMessage());
        }
    }
}
