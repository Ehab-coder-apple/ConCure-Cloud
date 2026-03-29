<?php

namespace App\Http\Controllers;

use App\Models\CanalTreatment;
use App\Models\DentalTreatment;
use App\Models\DentalChart;
use App\Models\DentalProcedure;
use App\Models\Patient;
use App\Models\User;
use App\Services\CustomTemplateService;
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can create treatment plans.');
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can create treatment plans.');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'dental_chart_id' => 'nullable|exists:dental_charts,id',
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable', // Accept both string and array
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

        // Normalize tooth_numbers (accept string "11,12" or array; store null when empty)
        $toothNumbers = $this->normalizeToothNumbers($request->tooth_numbers);

        try {
            DB::beginTransaction();

            $treatment = DentalTreatment::create([
                'patient_id' => $request->patient_id,
                'clinic_id' => $user->clinic_id,
                'dental_chart_id' => $request->dental_chart_id,
                'tooth_number' => $request->tooth_number,
                'tooth_numbers' => $toothNumbers,
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

            // Save canal treatment data if provided
            if ($request->has('canals') && is_array($request->canals)) {
                foreach ($request->canals as $canalData) {
                    if (empty($canalData['canal_name']) || empty($canalData['tooth_number'])) continue;

                    CanalTreatment::create([
                        'dental_treatment_id' => $treatment->id,
                        'patient_id' => $treatment->patient_id,
                        'clinic_id' => $treatment->clinic_id,
                        'tooth_number' => $canalData['tooth_number'],
                        'canal_name' => $canalData['canal_name'],
                        'working_length' => $canalData['working_length'] ?? null,
                        'master_apical_file' => $canalData['master_apical_file'] ?? null,
                        'master_cone_size' => $canalData['master_cone_size'] ?? null,
                        'taper' => $canalData['taper'] ?? null,
                        'status' => $canalData['status'] ?? 'not_started',
                        'notes' => $canalData['notes'] ?? null,
                        'created_by' => $user->id,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('dental.treatments.show', $treatment)
                           ->with('success', 'Treatment plan created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
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
            'creator',
            'dentalLabRequests.externalLab'
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can edit treatment plans.');
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can update treatment plans.');
        }

        $request->validate([
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable', // Accept both string and array
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

        // Convert/normalize tooth_numbers (avoid saving empty string into JSON column)
        $data = $request->except(['patient_id', 'clinic_id', 'treatment_number']);
        if (array_key_exists('tooth_numbers', $data)) {
            $data['tooth_numbers'] = $this->normalizeToothNumbers($data['tooth_numbers']);
        }

        $dentalTreatment->update($data);

        return redirect()->route('dental.treatments.show', $dentalTreatment)
                       ->with('success', 'Treatment plan updated successfully.');
    }

    /**
     * Normalize tooth_numbers from request into an array or null.
     * Accepts comma-separated string or array; returns null when empty.
     */
    private function normalizeToothNumbers($toothNumbers): ?array
    {
        if ($toothNumbers === null) {
            return null;
        }

        if (is_string($toothNumbers)) {
            $toothNumbers = trim($toothNumbers);
            if ($toothNumbers === '') {
                return null;
            }
            $toothNumbers = explode(',', $toothNumbers);
        }

        if (!is_array($toothNumbers)) {
            return null;
        }

        $toothNumbers = array_values(array_filter(array_map(function ($t) {
            return trim((string) $t);
        }, $toothNumbers), function ($t) {
            return $t !== '';
        }));

        return count($toothNumbers) > 0 ? $toothNumbers : null;
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can complete treatments.');
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

        $filename = 'treatment-plan-' . $dentalTreatment->treatment_number . '.pdf';

        // Check for custom template
        $forceCustom = request()->query('template') === 'custom';
        $clinic = $user->clinic;
        $templateData = $clinic ? CustomTemplateService::prepareTemplate($clinic, 'dental', $forceCustom) : null;

        if ($templateData) {
            $mpdf = CustomTemplateService::createMpdf($templateData);
            $html = view('dental.treatments.pdf-custom-template', [
                'dentalTreatment' => $dentalTreatment,
                'templateImagePath' => $templateData['imagePath'],
                'tplSettings' => $templateData['settings'],
            ])->render();
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');
            CustomTemplateService::cleanup($templateData);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $pdf = Pdf::loadView('dental.treatments.pdf', compact('dentalTreatment'));
        return $pdf->download($filename);
    }
}
