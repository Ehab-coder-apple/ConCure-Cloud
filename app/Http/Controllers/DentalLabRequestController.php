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

class DentalLabRequestController extends Controller
{
    /**
     * Display a listing of dental lab requests.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = DentalLabRequest::with(['patient', 'externalLab', 'doctor', 'dentalTreatment']);

        // Filter by clinic
        if ($user->clinic_id) {
            $query->byClinic($user->clinic_id);
        }

        // Filter by doctor if user is a doctor or dental_dept
        if ($user->role === 'doctor' || $user->role === 'dental_dept') {
            $query->byDoctor($user->id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
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
            'patient_id' => 'required|exists:patients,id',
            'dental_treatment_id' => 'nullable|exists:dental_treatments,id',
            'doctor_id' => 'required|exists:users,id',
            'external_lab_id' => 'nullable|exists:external_labs,id',
            'work_type' => 'required|in:crown,bridge,denture_full,denture_partial,implant_crown,implant_bridge,veneer,inlay_onlay,orthodontic_appliance,night_guard,sports_guard,temporary_crown,other',
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable|array',
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

        $labRequest->load([
            'patient',
            'dentalTreatment',
            'doctor',
            'externalLab',
            'receivedBy'
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can edit lab requests.');
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

        // Get dental labs
        $dentalLabs = ExternalLab::dental()
                                 ->active()
                                 ->when($user->clinic_id, fn($q) => $q->byClinic($user->clinic_id))
                                 ->ordered()
                                 ->get();

        // Get dental treatments for the clinic
        $treatments = DentalTreatment::query()
                                    ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                                    ->whereIn('status', ['planned', 'in_progress', 'completed'])
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        // Get users for received_by field
        $users = User::query()
                    ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                    ->where('is_active', true)
                    ->orderBy('first_name')
                    ->get();

        return view('dental.lab-requests.edit', compact(
            'labRequest',
            'patients',
            'treatments',
            'doctors',
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'lab_dept', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, dentists, and lab staff can update lab requests.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'dental_treatment_id' => 'nullable|exists:dental_treatments,id',
            'doctor_id' => 'required|exists:users,id',
            'external_lab_id' => 'nullable|exists:external_labs,id',
            'work_type' => 'required|in:crown,bridge,denture_full,denture_partial,implant_crown,implant_bridge,veneer,inlay_onlay,orthodontic_appliance,night_guard,sports_guard,temporary_crown,other',
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable|array',
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
