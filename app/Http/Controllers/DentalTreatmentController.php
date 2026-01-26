<?php

namespace App\Http\Controllers;

use App\Models\DentalTreatment;
use App\Models\DentalChart;
use App\Models\DentalProcedure;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DentalTreatmentController extends Controller
{
    /**
     * Display a listing of dental treatments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = DentalTreatment::with(['patient', 'assignedDoctor', 'dentalChart']);

        // Filter by clinic
        if ($user->clinic_id) {
            $query->byClinic($user->clinic_id);
        }

        // Filter by doctor if user is a doctor
        if ($user->role === 'doctor') {
            $query->byDoctor($user->id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('payment_status')) {
            $query->byPaymentStatus($request->payment_status);
        }

        if ($request->filled('patient_id')) {
            $query->byPatient($request->patient_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $treatments = $query->paginate(20);

        // Get patients for filter dropdown
        $patients = Patient::query()
                          ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        return view('dental.treatments.index', compact('treatments', 'patients'));
    }

    /**
     * Show the form for creating a new dental treatment.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors and dental assistants can create treatment plans.');
        }

        // Get patients for dropdown
        $patients = Patient::query()
                          ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        // Get doctors for assignment
        $doctors = User::where('role', 'doctor')
                      ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                      ->where('is_active', true)
                      ->orderBy('first_name')
                      ->get();

        // Get dental procedures
        $procedures = DentalProcedure::getAvailableForClinic($user->clinic_id)
                                    ->get();

        // If patient_id is provided, get their latest dental chart
        $patient = null;
        $dentalChart = null;
        if ($request->filled('patient_id')) {
            $patient = Patient::find($request->patient_id);
            if ($patient) {
                $dentalChart = $patient->latest_dental_chart;
            }
        }

        return view('dental.treatments.create', compact('patients', 'doctors', 'procedures', 'patient', 'dentalChart'));
    }

    /**
     * Store a newly created dental treatment.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors and dental assistants can create treatment plans.');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'dental_chart_id' => 'nullable|exists:dental_charts,id',
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable|array',
            'procedure_name' => 'required|string|max:255',
            'procedure_code' => 'nullable|string|max:50',
            'diagnosis' => 'nullable|string',
            'icd10_code' => 'nullable|string|max:20',
            'surfaces_affected' => 'nullable|array',
            'description' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'severity' => 'nullable|in:mild,moderate,severe',
            'scheduled_date' => 'nullable|date',
            'assigned_doctor_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $treatment = DentalTreatment::create([
                'patient_id' => $request->patient_id,
                'clinic_id' => $user->clinic_id,
                'dental_chart_id' => $request->dental_chart_id,
                'tooth_number' => $request->tooth_number,
                'tooth_numbers' => $request->tooth_numbers,
                'procedure_name' => $request->procedure_name,
                'procedure_code' => $request->procedure_code,
                'diagnosis' => $request->diagnosis,
                'icd10_code' => $request->icd10_code,
                'surfaces_affected' => $request->surfaces_affected,
                'description' => $request->description,
                'estimated_cost' => $request->estimated_cost,
                'currency' => $request->currency ?? 'USD',
                'estimated_duration_minutes' => $request->estimated_duration_minutes,
                'status' => $request->status,
                'priority' => $request->priority,
                'severity' => $request->severity,
                'scheduled_date' => $request->scheduled_date,
                'assigned_doctor_id' => $request->assigned_doctor_id ?? $user->id,
                'payment_status' => 'unpaid',
                'paid_amount' => 0,
                'notes' => $request->notes,
                'created_by' => $user->id,
            ]);

            return redirect()->route('dental.treatments.show', $treatment)
                           ->with('success', 'Treatment plan created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to create treatment plan. Please try again.');
        }
    }

    /**
     * Display the specified dental treatment.
     */
    public function show(DentalTreatment $dentalTreatment)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $dentalTreatment->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to treatment.');
            }
        }

        $dentalTreatment->load([
            'patient',
            'dentalChart.toothRecords',
            'assignedDoctor',
            'performedBy',
            'creator'
        ]);

        return view('dental.treatments.show', compact('dentalTreatment'));
    }

    /**
     * Show the form for editing the dental treatment.
     */
    public function edit(DentalTreatment $dentalTreatment)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $dentalTreatment->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to edit treatment.');
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors and dental assistants can edit treatment plans.');
        }

        // Get doctors for assignment
        $doctors = User::where('role', 'doctor')
                      ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                      ->where('is_active', true)
                      ->orderBy('first_name')
                      ->get();

        // Get dental procedures
        $procedures = DentalProcedure::getAvailableForClinic($user->clinic_id)
                                    ->get();

        return view('dental.treatments.edit', compact('dentalTreatment', 'doctors', 'procedures'));
    }

    /**
     * Update the specified dental treatment.
     */
    public function update(Request $request, DentalTreatment $dentalTreatment)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $dentalTreatment->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to update treatment.');
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors and dental assistants can update treatment plans.');
        }

        $request->validate([
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable|array',
            'procedure_name' => 'required|string|max:255',
            'procedure_code' => 'nullable|string|max:50',
            'diagnosis' => 'nullable|string',
            'icd10_code' => 'nullable|string|max:20',
            'surfaces_affected' => 'nullable|array',
            'description' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'severity' => 'nullable|in:mild,moderate,severe',
            'scheduled_date' => 'nullable|date',
            'assigned_doctor_id' => 'nullable|exists:users,id',
            'payment_status' => 'required|in:unpaid,partial,paid',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'post_treatment_notes' => 'nullable|string',
        ]);

        $dentalTreatment->update($request->except(['patient_id', 'clinic_id', 'treatment_number']));

        return redirect()->route('dental.treatments.show', $dentalTreatment)
                       ->with('success', 'Treatment plan updated successfully.');
    }

    /**
     * Remove the specified dental treatment.
     */
    public function destroy(DentalTreatment $dentalTreatment)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $dentalTreatment->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to delete treatment.');
            }
        }

        // Only admins can delete
        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can delete treatment plans.');
        }

        $dentalTreatment->delete();

        return redirect()->route('dental.treatments.index')
                       ->with('success', 'Treatment plan deleted successfully.');
    }

    /**
     * Mark treatment as completed.
     */
    public function markAsCompleted(Request $request, DentalTreatment $dentalTreatment)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $dentalTreatment->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to complete treatment.');
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors and dental assistants can complete treatments.');
        }

        $request->validate([
            'actual_cost' => 'nullable|numeric|min:0',
            'post_treatment_notes' => 'nullable|string',
        ]);

        $dentalTreatment->update([
            'status' => 'completed',
            'completed_date' => now(),
            'performed_by_id' => $user->id,
            'actual_cost' => $request->actual_cost ?? $dentalTreatment->estimated_cost,
            'post_treatment_notes' => $request->post_treatment_notes,
        ]);

        return back()->with('success', 'Treatment marked as completed.');
    }

    /**
     * Generate PDF for treatment plan.
     */
    public function pdf(DentalTreatment $dentalTreatment)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $dentalTreatment->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to treatment PDF.');
            }
        }

        $dentalTreatment->load([
            'patient',
            'dentalChart.toothRecords',
            'assignedDoctor',
            'performedBy',
            'creator'
        ]);

        $pdf = Pdf::loadView('dental.treatments.pdf', compact('dentalTreatment'));

        return $pdf->download('treatment-plan-' . $dentalTreatment->treatment_number . '.pdf');
    }
}
