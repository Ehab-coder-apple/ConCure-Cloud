<?php

namespace App\Http\Controllers;

use App\Models\AudiometryTest;
use App\Models\EntRecord;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AudiometryController extends Controller
{
    /**
     * Display a listing of all audiometry tests.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = AudiometryTest::with(['patient', 'performer', 'entRecord'])
            ->where('clinic_id', $user->clinic_id)
            ->orderBy('test_date', 'desc');

        // Filter by patient if specified
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($patientQuery) use ($search) {
                    $patientQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('patient_id', 'like', "%{$search}%");
                })
                ->orWhere('test_type', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $audiometryTests = $query->paginate(20);

        $patients = Patient::where('clinic_id', $user->clinic_id)
            ->orderBy('first_name')
            ->get();

        return view('ent.audiometry.index', compact('audiometryTests', 'patients'));
    }

    /**
     * Show the form for creating a new audiometry test.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        $entRecordId = $request->input('ent_record_id');
        $patientId = $request->input('patient_id');

        $entRecord = null;
        $patient = null;

        if ($entRecordId) {
            $entRecord = EntRecord::findOrFail($entRecordId);
            if ($entRecord->clinic_id !== $user->clinic_id) {
                abort(403);
            }
            $patient = $entRecord->patient;
        } elseif ($patientId) {
            $patient = Patient::findOrFail($patientId);
            if ($patient->clinic_id !== $user->clinic_id) {
                abort(403);
            }
        }

        // Get all patients for the dropdown (when no patient is pre-selected)
        $patients = Patient::where('clinic_id', $user->clinic_id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'patient_id', 'first_name', 'last_name']);

        return view('ent.audiometry.create', compact('entRecord', 'patient', 'patients'));
    }

    /**
     * Store a newly created audiometry test.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'ent_record_id' => 'nullable|exists:ent_records,id',
            'test_date' => 'required|date',
            'test_type' => 'required|in:pure_tone,speech,tympanometry,other',
            'right_ear_data' => 'nullable|array',
            'left_ear_data' => 'nullable|array',
            'right_srt' => 'nullable|integer|min:0|max:120',
            'left_srt' => 'nullable|integer|min:0|max:120',
            'right_wrs' => 'nullable|integer|min:0|max:100',
            'left_wrs' => 'nullable|integer|min:0|max:100',
            'right_tympanometry' => 'nullable|string|max:100',
            'left_tympanometry' => 'nullable|string|max:100',
            'right_interpretation' => 'nullable|in:normal,conductive_loss,sensorineural_loss,mixed_loss',
            'left_interpretation' => 'nullable|in:normal,conductive_loss,sensorineural_loss,mixed_loss',
            'notes' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        $audiometryTest = AudiometryTest::create([
            'patient_id' => $request->patient_id,
            'clinic_id' => $user->clinic_id,
            'ent_record_id' => $request->ent_record_id,
            'test_date' => $request->test_date,
            'test_type' => $request->test_type,
            'right_ear_data' => $request->right_ear_data,
            'left_ear_data' => $request->left_ear_data,
            'right_srt' => $request->right_srt,
            'left_srt' => $request->left_srt,
            'right_wrs' => $request->right_wrs,
            'left_wrs' => $request->left_wrs,
            'right_tympanometry' => $request->right_tympanometry,
            'left_tympanometry' => $request->left_tympanometry,
            'right_interpretation' => $request->right_interpretation,
            'left_interpretation' => $request->left_interpretation,
            'notes' => $request->notes,
            'recommendations' => $request->recommendations,
            'performed_by' => $user->id,
            'created_by' => $user->id,
        ]);

        if ($request->ent_record_id) {
            return redirect()->route('ent.show', $request->ent_record_id)
                ->with('success', 'Audiometry test created successfully.');
        }

        return redirect()->route('patients.show', $request->patient_id)
            ->with('success', 'Audiometry test created successfully.');
    }

    /**
     * Display the specified audiometry test.
     */
    public function show(AudiometryTest $audiometryTest)
    {
        $user = Auth::user();

        if ($audiometryTest->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        $audiometryTest->load(['patient', 'performer', 'entRecord']);

        return view('ent.audiometry.show', compact('audiometryTest'));
    }

    /**
     * Show the form for editing the audiometry test.
     */
    public function edit(AudiometryTest $audiometryTest)
    {
        $user = Auth::user();

        if ($audiometryTest->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        return view('ent.audiometry.edit', compact('audiometryTest'));
    }

    /**
     * Update the specified audiometry test.
     */
    public function update(Request $request, AudiometryTest $audiometryTest)
    {
        $user = Auth::user();

        if ($audiometryTest->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        $request->validate([
            'test_date' => 'required|date',
            'test_type' => 'required|in:pure_tone,speech,tympanometry,other',
            'right_ear_data' => 'nullable|array',
            'left_ear_data' => 'nullable|array',
        ]);

        $audiometryTest->update($request->all());

        return redirect()->route('audiometry.show', $audiometryTest)
            ->with('success', 'Audiometry test updated successfully.');
    }
}
