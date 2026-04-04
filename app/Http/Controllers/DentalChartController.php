<?php

namespace App\Http\Controllers;

use App\Models\DentalChart;
use App\Models\DentalToothRecord;
use App\Models\DentalTreatment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DentalChartController extends Controller
{
    /**
     * Display a listing of all dental charts across all patients.
     */
    public function allCharts(Request $request)
    {
        $user = Auth::user();

        // Build query
        $query = DentalChart::with(['patient', 'creator', 'toothRecords']);

        // Filter by clinic if not super admin
        if (!$user->isSuperAdmin()) {
            $query->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id));
        }

        // Filter by creator for dental_dept role - they only see their own charts
        if ($user->role === 'dental_dept') {
            $query->byCreator($user->id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by chart type
        if ($request->filled('chart_type')) {
            $query->where('chart_type', $request->chart_type);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $charts = $query->paginate(20);

        return view('dental.charts.all', compact('charts'));
    }

    /**
     * Display a listing of dental charts for a patient.
     */
    public function index(Request $request, Patient $patient)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to patient dental charts.');
            }
        }

        $charts = DentalChart::where('patient_id', $patient->id)
                            ->with(['creator', 'toothRecords'])
                            ->latest()
                            ->paginate(10);

        return view('dental.charts.index', compact('patient', 'charts'));
    }

    /**
     * Show the form for creating a new dental chart.
     */
    public function create(Patient $patient)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to create dental chart.');
            }
        }

        // Check if user is doctor or dental assistant
        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can create dental charts.');
        }

        return view('dental.charts.create', compact('patient'));
    }

    /**
     * Store a newly created dental chart.
     */
    public function store(Request $request, Patient $patient)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to create dental chart.');
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can create dental charts.');
        }

        $request->validate([
            'chart_type' => 'required|in:adult,pediatric',
            'general_notes' => 'nullable|string',
            'tooth_records' => 'nullable|array',
            'tooth_records.*.tooth_number' => 'required_with:tooth_records|string',
            'tooth_records.*.primary_condition' => 'required_with:tooth_records|string',
            'tooth_records.*.conditions' => 'nullable|array',
            'tooth_records.*.surfaces_affected' => 'nullable|array',
            'tooth_records.*.severity' => 'nullable|in:mild,moderate,severe',
            'tooth_records.*.notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $dentalChart = DentalChart::create([
                'patient_id' => $patient->id,
                'clinic_id' => $user->clinic_id,
                'chart_type' => $request->chart_type,
                'general_notes' => $request->general_notes,
                'created_by' => $user->id,
            ]);

            // Create tooth records if provided
            if ($request->filled('tooth_records')) {
                foreach ($request->tooth_records as $toothData) {
                    DentalToothRecord::create([
                        'dental_chart_id' => $dentalChart->id,
                        'tooth_number' => $toothData['tooth_number'],
                        'primary_condition' => $toothData['primary_condition'],
                        'conditions' => $toothData['conditions'] ?? [$toothData['primary_condition']],
                        'surfaces_affected' => $toothData['surfaces_affected'] ?? [],
                        'severity' => $toothData['severity'] ?? null,
                        'notes' => $toothData['notes'] ?? null,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);
                }
            }

            DB::commit();

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dental chart created successfully.',
                    'chart' => [
                        'id' => $dentalChart->id,
                        'chart_type' => $dentalChart->chart_type,
                        'created_at' => $dentalChart->created_at->format('M d, Y'),
                    ]
                ]);
            }

            return redirect()->route('dental.charts.show', ['patient' => $patient, 'dentalChart' => $dentalChart])
                           ->with('success', 'Dental chart created successfully.');

        } catch (\Exception $e) {
            DB::rollback();

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create dental chart: ' . $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                        ->with('error', 'Failed to create dental chart. Please try again.');
        }
    }

    /**
     * Display the specified dental chart.
     */
    public function show(Patient $patient, DentalChart $dentalChart)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to dental chart.');
            }
        }

        // Sync all planned/in-progress treatments to the dental chart
        $this->syncPlannedTreatmentsToChart($patient, $dentalChart, $user);

        $dentalChart->load(['creator', 'toothRecords.creator', 'toothRecords.updater', 'treatments', 'images']);

        // Check if detailed view is requested (default to simple view with improvements)
        $viewType = request()->query('view', 'simple');
        $viewName = $viewType === 'detailed' ? 'dental.charts.show' : 'dental.charts.show-simple';

        return view($viewName, compact('patient', 'dentalChart'));
    }

    /**
     * Show the form for editing the dental chart.
     */
    public function edit(Patient $patient, DentalChart $dentalChart)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to edit dental chart.');
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can edit dental charts.');
        }

        $dentalChart->load(['toothRecords']);

        return view('dental.charts.edit', compact('patient', 'dentalChart'));
    }

    /**
     * Update the specified dental chart.
     */
    public function update(Request $request, Patient $patient, DentalChart $dentalChart)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to update dental chart.');
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can update dental charts.');
        }

        $request->validate([
            'chart_type' => 'required|in:adult,pediatric',
            'general_notes' => 'nullable|string',
        ]);

        $dentalChart->update([
            'chart_type' => $request->chart_type,
            'general_notes' => $request->general_notes,
        ]);

        return redirect()->route('dental.charts.show', ['patient' => $patient, 'dentalChart' => $dentalChart])
                       ->with('success', 'Dental chart updated successfully.');
    }

    /**
     * Remove the specified dental chart.
     */
    public function destroy(Patient $patient, DentalChart $dentalChart)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to delete dental chart.');
            }
        }

        // Only admins can delete
        if (!in_array($user->role, ['admin', 'program_owner'])) {
            abort(403, 'Only administrators can delete dental charts.');
        }

        $dentalChart->delete();

        return redirect()->route('dental.charts.index', $patient)
                       ->with('success', 'Dental chart deleted successfully.');
    }

    /**
     * Update a specific tooth record (AJAX endpoint).
     */
    public function updateToothRecord(Request $request, Patient $patient, DentalChart $dentalChart)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'tooth_number' => 'required|string',
            'primary_condition' => 'required|string',
            'conditions' => 'nullable|array',
            'surfaces_affected' => 'nullable|array',
            'severity' => 'nullable|in:mild,moderate,severe',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Find existing record to preserve created_by
            $existingRecord = DentalToothRecord::where('dental_chart_id', $dentalChart->id)
                ->where('tooth_number', $request->tooth_number)
                ->first();

            $toothRecord = DentalToothRecord::updateOrCreate(
                [
                    'dental_chart_id' => $dentalChart->id,
                    'tooth_number' => $request->tooth_number,
                ],
                [
                    'primary_condition' => $request->primary_condition,
                    'conditions' => $request->conditions ?? [$request->primary_condition],
                    'surfaces_affected' => $request->surfaces_affected ?? [],
                    'severity' => $request->severity,
                    'notes' => $request->notes,
                    'created_by' => $existingRecord ? $existingRecord->created_by : $user->id,
                    'updated_by' => $user->id,
                ]
            );

            // Check if we should create treatment plans for new conditions
            $this->syncDentalChartToTreatmentPlan($dentalChart, $toothRecord, $existingRecord, $user);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tooth record updated successfully.',
                'tooth_record' => $toothRecord,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update tooth record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update tooth record: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF for dental chart.
     */
    public function pdf(Patient $patient, DentalChart $dentalChart)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to dental chart PDF.');
            }
        }

        $dentalChart->load(['creator', 'toothRecords']);

        $filename = 'dental-chart-' . $patient->patient_id . '-' . $dentalChart->created_at->format('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView('dental.charts.pdf', compact('patient', 'dentalChart'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($filename);
    }

    /**
     * Show dental chart history timeline.
     */
    public function history(Patient $patient)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to dental history.');
            }
        }

        $charts = DentalChart::where('patient_id', $patient->id)
                            ->with(['creator', 'toothRecords', 'treatments'])
                            ->latest()
                            ->get();

        return view('dental.charts.history', compact('patient', 'charts'));
    }

    /**
     * Sync dental chart changes to treatment plan.
     * Creates planned treatments for certain conditions if they don't exist.
     */
    private function syncDentalChartToTreatmentPlan(
        DentalChart $dentalChart,
        DentalToothRecord $toothRecord,
        ?DentalToothRecord $existingRecord,
        User $user
    ): void {
        try {
            $newConditions = $toothRecord->conditions ?? [];
            $oldConditions = $existingRecord ? ($existingRecord->conditions ?? []) : [];

            // Skip if setting to healthy or no new conditions
            if (in_array('healthy', $newConditions) && count($newConditions) === 1) {
                return;
            }

            // Find conditions that were just added
            $addedConditions = array_diff($newConditions, $oldConditions);

            // Skip if no new conditions added
            if (empty($addedConditions)) {
                return;
            }

            // Only create treatment plans for conditions that need treatment
            $treatableConditions = ['caries', 'fracture', 'periodontal'];

            foreach ($addedConditions as $condition) {
                if (!in_array($condition, $treatableConditions)) {
                    continue;
                }

                // Check if a planned treatment already exists for this tooth and condition
                $existingTreatment = DentalTreatment::where('dental_chart_id', $dentalChart->id)
                    ->where('patient_id', $dentalChart->patient_id)
                    ->where(function($q) use ($toothRecord) {
                        $q->where('tooth_number', $toothRecord->tooth_number)
                          ->orWhereJsonContains('tooth_numbers', $toothRecord->tooth_number);
                    })
                    ->whereIn('status', ['planned', 'in_progress'])
                    ->where('procedure_name', 'LIKE', '%' . $this->mapConditionToProcedure($condition) . '%')
                    ->first();

                if ($existingTreatment) {
                    continue; // Treatment already planned
                }

                // Get clinic currency
                $clinicCurrency = DB::table('settings')
                    ->where('clinic_id', $user->clinic_id)
                    ->where('key', 'currency')
                    ->value('value') ?? 'USD';

                // Create a planned treatment
                DentalTreatment::create([
                    'patient_id' => $dentalChart->patient_id,
                    'clinic_id' => $user->clinic_id,
                    'dental_chart_id' => $dentalChart->id,
                    'tooth_number' => $toothRecord->tooth_number,
                    'tooth_numbers' => [$toothRecord->tooth_number],
                    'procedure_name' => $this->mapConditionToProcedure($condition),
                    'diagnosis' => ucfirst(str_replace('_', ' ', $condition)),
                    'surfaces_affected' => $toothRecord->surfaces_affected ?? [],
                    'status' => 'planned',
                    'priority' => $this->mapSeverityToPriority($toothRecord->severity),
                    'severity' => $toothRecord->severity,
                    'currency' => $clinicCurrency,
                    'assigned_doctor_id' => $user->id,
                    'payment_status' => 'unpaid',
                    'paid_amount' => 0,
                    'notes' => 'Auto-created from dental chart update',
                    'created_by' => $user->id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to sync dental chart to treatment plan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'dental_chart_id' => $dentalChart->id,
                'tooth_number' => $toothRecord->tooth_number,
            ]);
            // Don't throw the exception - just log it and continue
            // This prevents the entire tooth record update from failing
        }
    }

    /**
     * Map dental condition to procedure name.
     */
    private function mapConditionToProcedure(string $condition): string
    {
        return match($condition) {
            'caries' => 'Filling / Restoration',
            'fracture' => 'Crown or Restoration',
            'periodontal' => 'Periodontal Treatment',
            default => ucfirst(str_replace('_', ' ', $condition)) . ' Treatment',
        };
    }

    /**
     * Map severity to treatment priority.
     */
    private function mapSeverityToPriority(?string $severity): string
    {
        return match($severity) {
            'severe' => 'urgent',
            'moderate' => 'high',
            'mild' => 'medium',
            default => 'medium',
        };
    }

    /**
     * Sync planned/in-progress treatments to dental chart.
     * This ensures all treatment plans are visible on the dental chart.
     */
    private function syncPlannedTreatmentsToChart(Patient $patient, DentalChart $dentalChart, User $user): void
    {
        try {
            // Get all treatments for this patient that are planned or in progress
            $treatments = DentalTreatment::where('patient_id', $patient->id)
                ->whereIn('status', ['planned', 'in_progress'])
                ->get();

            foreach ($treatments as $treatment) {
                // Get all affected teeth
                $toothNumbers = [];
                if ($treatment->tooth_number) {
                    $toothNumbers[] = $treatment->tooth_number;
                }
                if ($treatment->tooth_numbers && is_array($treatment->tooth_numbers)) {
                    $toothNumbers = array_merge($toothNumbers, $treatment->tooth_numbers);
                }
                $toothNumbers = array_unique($toothNumbers);

                // Map procedure to condition
                $condition = $this->mapProcedureToConditionForChart($treatment->procedure_name);

                foreach ($toothNumbers as $toothNumber) {
                    if (empty($toothNumber)) continue;

                    // Check if tooth record already exists
                    $existingRecord = DentalToothRecord::where('dental_chart_id', $dentalChart->id)
                        ->where('tooth_number', $toothNumber)
                        ->first();

                    if ($existingRecord) {
                        // Add condition if not already present
                        $conditions = $existingRecord->conditions ?? [];
                        if (!in_array($condition, $conditions)) {
                            $conditions[] = $condition;
                            $existingRecord->update([
                                'conditions' => $conditions,
                                'updated_by' => $user->id,
                            ]);
                        }
                    } else {
                        // Create new tooth record with the condition
                        DentalToothRecord::create([
                            'dental_chart_id' => $dentalChart->id,
                            'tooth_number' => $toothNumber,
                            'primary_condition' => $condition,
                            'conditions' => [$condition],
                            'surfaces_affected' => $treatment->surfaces_affected ?? [],
                            'severity' => $treatment->severity,
                            'notes' => "Planned: {$treatment->procedure_name} (#{$treatment->treatment_number})",
                            'created_by' => $user->id,
                            'updated_by' => $user->id,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to sync planned treatments to chart', [
                'error' => $e->getMessage(),
                'patient_id' => $patient->id,
                'chart_id' => $dentalChart->id,
            ]);
            // Don't throw - just log and continue
        }
    }

    /**
     * Map procedure name to dental chart condition.
     */
    private function mapProcedureToConditionForChart(string $procedureName): string
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

        // Caries
        if (str_contains($procedureLower, 'caries') ||
            str_contains($procedureLower, 'cavity')) {
            return 'caries';
        }

        // Fracture
        if (str_contains($procedureLower, 'fracture')) {
            return 'fracture';
        }

        // Periodontal
        if (str_contains($procedureLower, 'periodontal') ||
            str_contains($procedureLower, 'gum') ||
            str_contains($procedureLower, 'gingival')) {
            return 'periodontal';
        }

        // Default to 'other'
        return 'other';
    }
}
