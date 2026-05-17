<?php

namespace App\Http\Controllers;

use App\Models\CanalTreatment;
use App\Models\DentalTreatment;
use App\Models\DentalChart;
use App\Models\DentalProcedure;
use App\Models\DentalToothRecord;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Receipt;
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

        // Dental designers and lab technicians cannot access treatment plans
        if (in_array($user->role, ['dental_designer', 'dental_lab_technician'])) {
            abort(403, 'Dental designers and lab technicians do not have access to treatment plans.');
        }

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

        // Get clinic currency setting
        $clinicCurrency = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD';

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'dental_chart_id' => 'nullable|exists:dental_charts,id',
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable', // Accept both string and array
            'procedure_ids' => 'required|array|min:1',
            'procedure_ids.*' => 'required|exists:dental_procedures,id',
            'diagnosis' => 'nullable|string',
            'icd10_code' => 'nullable|string|max:20',
            'surfaces_affected' => 'nullable|array',
            'description' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:' . $clinicCurrency,
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'severity' => 'nullable|in:mild,moderate,severe',
            'scheduled_date' => 'nullable|date',
            'assigned_doctor_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ], [
            'currency.in' => 'The currency must match the clinic\'s configured currency (' . $clinicCurrency . ').',
            'procedure_ids.required' => 'Please select at least one procedure.',
            'procedure_ids.min' => 'Please select at least one procedure.',
        ]);

        // Normalize tooth_numbers (accept string "11,12" or array; store null when empty)
        $toothNumbers = $this->normalizeToothNumbers($request->tooth_numbers);

        try {
            DB::beginTransaction();

            // Get all selected procedures
            $procedures = DentalProcedure::whereIn('id', $request->procedure_ids)->get();
            $createdTreatments = [];

            // Create a treatment plan for each selected procedure
            foreach ($procedures as $procedure) {
                $treatment = DentalTreatment::create([
                    'patient_id' => $request->patient_id,
                    'clinic_id' => $user->clinic_id,
                    'dental_chart_id' => $request->dental_chart_id,
                    'tooth_number' => $request->tooth_number,
                    'tooth_numbers' => $toothNumbers,
                    'procedure_name' => $procedure->name,
                    'procedure_code' => $procedure->code,
                    'diagnosis' => $request->diagnosis,
                    'icd10_code' => $request->icd10_code,
                    'surfaces_affected' => $request->surfaces_affected,
                    'description' => $request->description ?: $procedure->description,
                    'estimated_cost' => $procedure->default_cost ?? 0,
                    'currency' => $clinicCurrency,
                    'estimated_duration_minutes' => $procedure->estimated_duration_minutes,
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

                // Sync financial data with Finance module if cost is provided
                if ($treatment->estimated_cost > 0) {
                    $this->syncTreatmentFinancialData($treatment, $user);
                }

                $createdTreatments[] = $treatment;
            }

            // Save canal treatment data if provided (only for the first treatment if root canal)
            if ($request->has('canals') && is_array($request->canals) && count($createdTreatments) > 0) {
                $firstTreatment = $createdTreatments[0];
                foreach ($request->canals as $canalData) {
                    if (empty($canalData['canal_name']) || empty($canalData['tooth_number'])) continue;

                    CanalTreatment::create([
                        'dental_treatment_id' => $firstTreatment->id,
                        'patient_id' => $firstTreatment->patient_id,
                        'clinic_id' => $firstTreatment->clinic_id,
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

            $count = count($createdTreatments);
            $message = $count === 1
                ? 'Treatment plan created successfully.'
                : "{$count} treatment plans created successfully.";

            // Redirect to the index page if multiple treatments, or to the single treatment if only one
            if ($count === 1) {
                return redirect()->route('dental.treatments.show', $createdTreatments[0])
                               ->with('success', $message);
            } else {
                return redirect()->route('dental.treatments.index')
                               ->with('success', $message);
            }

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

        // Get clinic currency setting
        $clinicCurrency = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD';

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
            'currency' => 'nullable|string|in:' . $clinicCurrency,
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
        ], [
            'currency.in' => 'The currency must match the clinic\'s configured currency (' . $clinicCurrency . ').',
        ]);

        // Convert/normalize tooth_numbers (avoid saving empty string into JSON column)
        $data = $request->except(['patient_id', 'clinic_id', 'treatment_number']);
        if (array_key_exists('tooth_numbers', $data)) {
            $data['tooth_numbers'] = $this->normalizeToothNumbers($data['tooth_numbers']);
        }

        DB::transaction(function () use ($dentalTreatment, $data, $user) {
            // Track if status is changing to completed
            $wasCompleted = $dentalTreatment->status === 'completed';
            $isNowCompleted = ($data['status'] ?? $dentalTreatment->status) === 'completed';

            $dentalTreatment->update($data);

            // Sync with dental chart if newly completed
            if (!$wasCompleted && $isNowCompleted) {
                $this->syncTreatmentToDentalChart($dentalTreatment, $user);
            }

            // Sync financial data with Finance module
            $this->syncTreatmentFinancialData($dentalTreatment, $user);
        });

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
     * Sync dental treatment financial data with the Finance module.
     * Creates or updates Invoice and Receipt records based on treatment costs and payments.
     */
    private function syncTreatmentFinancialData(DentalTreatment $treatment, User $user): void
    {
        // Only sync if there's financial data
        $totalCost = $treatment->actual_cost ?? $treatment->estimated_cost ?? 0;
        if ($totalCost <= 0) {
            return;
        }

        $paidAmount = $treatment->paid_amount ?? 0;

        // Create or update invoice
        if ($treatment->invoice_id) {
            // Update existing invoice
            $invoice = Invoice::find($treatment->invoice_id);
            if ($invoice) {
                // Update invoice item
                $item = $invoice->items()->first();
                if ($item) {
                    $item->update([
                        'description' => "Dental Treatment: {$treatment->procedure_name}",
                        'quantity' => 1,
                        'unit_price' => $totalCost,
                    ]);
                }

                // Update payment and status
                $invoice->paid_amount = $paidAmount;
                $invoice->calculateTotals();
                $invoice->updateStatus();
                $invoice->save();
            }
        } else {
            // Create new invoice
            $invoice = Invoice::create([
                'patient_id' => $treatment->patient_id,
                'clinic_id' => $treatment->clinic_id,
                'invoice_date' => now()->toDateString(),
                'due_date' => $treatment->scheduled_date ?? now()->addDays(30)->toDateString(),
                'subtotal' => 0,
                'tax_rate' => 0,
                'discount_rate' => 0,
                'discount_amount' => 0,
                'paid_amount' => $paidAmount,
                'status' => 'draft',
                'notes' => "Dental Treatment #{$treatment->treatment_number}",
                'created_by' => $user->id,
            ]);

            // Add invoice item
            $invoice->addItem([
                'description' => "Dental Treatment: {$treatment->procedure_name}",
                'quantity' => 1,
                'unit_price' => $totalCost,
                'item_type' => 'procedure',
            ]);

            // Update invoice status based on payment
            $invoice->updateStatus();
            $invoice->save();

            // Link invoice to treatment
            $treatment->invoice_id = $invoice->id;
            $treatment->saveQuietly(); // Save without triggering events
        }

        // Create receipt if there's a payment
        if ($paidAmount > 0) {
            // Check if receipt already exists for this treatment
            $existingReceipt = Receipt::where('reference_number', $treatment->treatment_number)->first();

            if ($existingReceipt) {
                // Update existing receipt
                $existingReceipt->update([
                    'amount' => $paidAmount,
                    'description' => "Payment for Dental Treatment: {$treatment->procedure_name}",
                ]);
            } else {
                // Create new receipt
                Receipt::create([
                    'clinic_id' => $treatment->clinic_id,
                    'description' => "Payment for Dental Treatment: {$treatment->procedure_name}",
                    'amount' => $paidAmount,
                    'category' => 'procedure_fee',
                    'receipt_date' => now()->toDateString(),
                    'payment_method' => 'cash', // Default, can be enhanced
                    'payer_name' => $treatment->patient ?
                        trim(($treatment->patient->first_name ?? '') . ' ' . ($treatment->patient->last_name ?? '')) : null,
                    'reference_number' => $treatment->treatment_number,
                    'notes' => "Dental treatment payment",
                    'created_by' => $user->id,
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
            }
        }
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

        DB::beginTransaction();
        try {
            $dentalTreatment->update([
                'status' => 'completed',
                'completed_date' => now(),
                'performed_by_id' => $user->id,
                'actual_cost' => $request->actual_cost ?? $dentalTreatment->estimated_cost,
                'post_treatment_notes' => $request->post_treatment_notes,
            ]);

            // Sync with dental chart
            $this->syncTreatmentToDentalChart($dentalTreatment, $user);

            DB::commit();
            return back()->with('success', 'Treatment marked as completed and dental chart updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to complete treatment and sync chart', [
                'treatment_id' => $dentalTreatment->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to complete treatment: ' . $e->getMessage());
        }
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

    /**
     * Sync completed treatment to dental chart.
     */
    private function syncTreatmentToDentalChart(DentalTreatment $treatment, User $user): void
    {
        // Get or create dental chart for the patient
        $dentalChart = $treatment->dental_chart_id
            ? DentalChart::find($treatment->dental_chart_id)
            : DentalChart::getLatestForPatient($treatment->patient_id);

        // If no dental chart exists, create one
        if (!$dentalChart) {
            $dentalChart = DentalChart::create([
                'patient_id' => $treatment->patient_id,
                'clinic_id' => $treatment->clinic_id,
                'chart_type' => 'adult', // Default to adult
                'created_by' => $user->id,
            ]);
        }

        // Link the treatment to the dental chart if not already linked
        if (!$treatment->dental_chart_id) {
            $treatment->update(['dental_chart_id' => $dentalChart->id]);
        }

        // Determine the condition based on procedure name
        $condition = $this->mapProcedureToCondition($treatment->procedure_name);

        // Get all tooth numbers affected
        $toothNumbers = [];
        if ($treatment->tooth_number) {
            $toothNumbers[] = $treatment->tooth_number;
        }
        if ($treatment->tooth_numbers && is_array($treatment->tooth_numbers)) {
            $toothNumbers = array_merge($toothNumbers, $treatment->tooth_numbers);
        }
        $toothNumbers = array_unique($toothNumbers);

        // Update tooth records for each affected tooth
        foreach ($toothNumbers as $toothNumber) {
            if (empty($toothNumber)) continue;

            // Find existing record to preserve created_by
            $existingRecord = DentalToothRecord::where('dental_chart_id', $dentalChart->id)
                ->where('tooth_number', $toothNumber)
                ->first();

            $conditions = $existingRecord && $existingRecord->conditions
                ? $existingRecord->conditions
                : [];

            // Add the new condition if not already present
            if (!in_array($condition, $conditions)) {
                $conditions[] = $condition;
            }

            DentalToothRecord::updateOrCreate(
                [
                    'dental_chart_id' => $dentalChart->id,
                    'tooth_number' => $toothNumber,
                ],
                [
                    'primary_condition' => $condition,
                    'conditions' => $conditions,
                    'surfaces_affected' => $treatment->surfaces_affected ?? [],
                    'severity' => $treatment->severity,
                    'notes' => "Treatment: {$treatment->procedure_name} (#{$treatment->treatment_number})",
                    'created_by' => $existingRecord ? $existingRecord->created_by : $user->id,
                    'updated_by' => $user->id,
                ]
            );
        }
    }

    /**
     * Map procedure name to dental condition.
     */
    private function mapProcedureToCondition(string $procedureName): string
    {
        $procedureLower = strtolower($procedureName);

        // Root canal treatment
        if (str_contains($procedureLower, 'root canal') ||
            str_contains($procedureLower, 'endodontic') ||
            str_contains($procedureLower, 'rct')) {
            return 'root_canal';
        }

        // Crown
        if (str_contains($procedureLower, 'crown')) {
            return 'crown';
        }

        // Filling
        if (str_contains($procedureLower, 'filling') ||
            str_contains($procedureLower, 'restoration') ||
            str_contains($procedureLower, 'composite')) {
            return 'filling';
        }

        // Extraction
        if (str_contains($procedureLower, 'extraction') ||
            str_contains($procedureLower, 'extract')) {
            return 'extraction';
        }

        // Implant
        if (str_contains($procedureLower, 'implant')) {
            return 'implant';
        }

        // Bridge
        if (str_contains($procedureLower, 'bridge')) {
            return 'bridge';
        }

        // Default to 'other'
        return 'other';
    }
}
