<?php

namespace App\Http\Controllers;

use App\Models\SurgicalCase;
use App\Models\SurgicalOperation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SurgicalCaseController extends Controller
{
    /**
     * List surgical cases for the current clinic.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = SurgicalCase::query()
            ->with(['patient', 'primarySurgeon']);

        if ($user->clinic_id) {
            $query->where('clinic_id', $user->clinic_id);
        }

        $cases = $query->latest('scheduled_at')
            ->latest('created_at')
            ->paginate(20);

        return view('surgery.cases.index', compact('cases'));
    }

    /**
     * Show form to create a new surgical case.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        $query = Patient::query();

        if ($user->clinic_id) {
            $query->where('clinic_id', $user->clinic_id);
        }

        $patients = $query->where(function($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->orderBy('first_name')
            ->get();

        $doctorQuery = User::whereIn('role', ['doctor', 'admin'])
            ->where(function($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            });

        if ($user->clinic_id) {
            $doctorQuery->where('clinic_id', $user->clinic_id);
        }

        $doctors = $doctorQuery->orderBy('first_name')->get();

        $preselectedPatientId = $request->get('patient_id');

        return view('surgery.cases.create', compact('patients', 'doctors', 'preselectedPatientId'));
    }

    /**
     * Store a new surgical case (no complex logic yet).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'primary_surgeon_id' => 'required|exists:users,id',
            'diagnosis' => 'nullable|string',
            'planned_procedure' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $patient = Patient::find($validated['patient_id']);

            SurgicalCase::create([
                'clinic_id' => $user->clinic_id ?? $patient->clinic_id,
                'patient_id' => $validated['patient_id'],
                'primary_surgeon_id' => $validated['primary_surgeon_id'],
                'diagnosis' => $validated['diagnosis'] ?? null,
                'planned_procedure' => $validated['planned_procedure'] ?? null,
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => 'planned',
                'created_by' => $user->id,
            ]);
        });

        return redirect()->route('surgery.index')
            ->with('success', 'Surgical case created (basic scaffold). You can now extend this module.');
    }

    /**
     * Display a single surgical case and its latest operation (if any).
     */
    public function show(SurgicalCase $surgicalCase)
    {
        $user = Auth::user();

        if ($user->clinic_id && $surgicalCase->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        $surgicalCase->load([
            'patient',
            'primarySurgeon',
            'operations' => function ($q) {
                $q->latest('operation_date');
            },
            'visits' => function ($q) {
                $q->latest('visit_date');
            },
        ]);

        $latestOperation = $surgicalCase->operations->first();

        // Debug: Log that we're returning the correct view
        \Log::info('Rendering surgery.cases.show for case #' . $surgicalCase->id);

        return view('surgery.cases.show', compact('surgicalCase', 'latestOperation'));
    }

    /**
     * Show form to record a surgical operation for a case.
     */
    public function createOperation(SurgicalCase $surgicalCase)
    {
        $user = Auth::user();

        if ($user->clinic_id && $surgicalCase->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        $surgicalCase->load('patient');

        return view('surgery.operations.create', compact('surgicalCase'));
    }

    /**
     * Store a new surgical operation (pre-op, operative note, post-op).
     */
    public function storeOperation(Request $request, SurgicalCase $surgicalCase)
    {
        $user = Auth::user();

        if ($user->clinic_id && $surgicalCase->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        $validated = $request->validate([
            'operation_date' => 'nullable|date',
            'theatre' => 'nullable|string|max:255',
            'asa_class' => 'nullable|string|max:10',
            'anesthesia_type' => 'nullable|string|max:50',
            'operative_note' => 'nullable|string',
            'preop_vitals' => 'nullable|string',
            'preop_notes' => 'nullable|string',
            'postop_status' => 'nullable|string',
            'postop_plan' => 'nullable|string',
            // Nested wound assessment structure within postop_assessment
            'wound_assessment' => 'nullable|array',
            'complications' => 'nullable|string',
            'estimated_blood_loss_ml' => 'nullable|integer|min:0',
        ]);

        $woundAssessment = $validated['wound_assessment'] ?? null;

        DB::transaction(function () use ($validated, $user, $surgicalCase, $woundAssessment) {
            SurgicalOperation::create([
                'clinic_id' => $surgicalCase->clinic_id,
                'patient_id' => $surgicalCase->patient_id,
                'surgical_case_id' => $surgicalCase->id,
                'operation_date' => $validated['operation_date'] ?? null,
                'theatre' => $validated['theatre'] ?? null,
                'asa_class' => $validated['asa_class'] ?? null,
                'anesthesia_type' => $validated['anesthesia_type'] ?? null,
                'preop_assessment' => [
                    'vitals_and_risk' => $validated['preop_vitals'] ?? null,
                    'notes' => $validated['preop_notes'] ?? null,
                ],
                'operative_note' => $validated['operative_note'] ?? null,
                'postop_assessment' => [
                    'status' => $validated['postop_status'] ?? null,
                    'plan' => $validated['postop_plan'] ?? null,
                    'wound_assessment' => $woundAssessment,
                ],
                'complications' => $validated['complications'] ?? null,
                'estimated_blood_loss_ml' => $validated['estimated_blood_loss_ml'] ?? null,
                'created_by' => $user->id,
            ]);

            // Mark case as in_progress / completed baseline logic
            if ($surgicalCase->status === 'planned') {
                $surgicalCase->status = 'completed';
                $surgicalCase->save();
            }
        });

        return redirect()->route('surgery.show', $surgicalCase)
            ->with('success', 'Surgical operation saved successfully.');
    }
}
