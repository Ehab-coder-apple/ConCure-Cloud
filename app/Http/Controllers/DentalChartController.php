<?php

namespace App\Http\Controllers;

use App\Models\DentalChart;
use App\Models\DentalToothRecord;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%");
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
        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors and dental assistants can create dental charts.');
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors and dental assistants can create dental charts.');
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

        $dentalChart->load(['creator', 'toothRecords.creator', 'toothRecords.updater', 'treatments', 'images']);

        return view('dental.charts.show', compact('patient', 'dentalChart'));
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors and dental assistants can edit dental charts.');
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors and dental assistants can update dental charts.');
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

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
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

        try {
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
                    'created_by' => $toothRecord->created_by ?? $user->id,
                    'updated_by' => $user->id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Tooth record updated successfully.',
                'tooth_record' => $toothRecord,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tooth record.',
            ], 500);
        }
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
}
