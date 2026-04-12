<?php

namespace App\Http\Controllers;

use App\Models\EntRecord;
use App\Models\Patient;
use App\Models\User;
use App\Models\AudiometryTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EntController extends Controller
{
    /**
     * Display a listing of ENT records.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = EntRecord::with(['patient', 'doctor'])
            ->where('clinic_id', $user->clinic_id)
            ->orderBy('visit_date', 'desc');

        // Filter by patient if specified
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('chief_complaint', 'like', "%{$search}%")
                  ->orWhere('diagnosis', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($patientQuery) use ($search) {
                      $patientQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('patient_id', 'like', "%{$search}%");
                  });
            });
        }

        $entRecords = $query->paginate(20);

        $patients = Patient::where('clinic_id', $user->clinic_id)
            ->orderBy('first_name')
            ->get();

        return view('ent.index', compact('entRecords', 'patients'));
    }

    /**
     * Show the form for creating a new ENT record.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        $patients = Patient::where('clinic_id', $user->clinic_id)
            ->orderBy('first_name')
            ->get();

        $doctors = User::where('clinic_id', $user->clinic_id)
            ->whereIn('role', ['doctor', 'admin'])
            ->orderBy('first_name')
            ->get();

        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = Patient::find($request->patient_id);
        }

        return view('ent.create', compact('patients', 'doctors', 'patient'));
    }

    /**
     * Store a newly created ENT record.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'ear_examination' => 'nullable|string',
            'nose_examination' => 'nullable|string',
            'throat_examination' => 'nullable|string',
            'neck_examination' => 'nullable|string',
            'cranial_nerves' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'icd10_code' => 'nullable|string|max:20',
            'treatment_plan' => 'nullable|string',
            'medications' => 'nullable|string',
            'followup_date' => 'nullable|date|after:visit_date',
            'notes' => 'nullable|string',
        ]);

        $entRecord = EntRecord::create([
            'patient_id' => $request->patient_id,
            'clinic_id' => $user->clinic_id,
            'doctor_id' => $request->doctor_id,
            'visit_date' => $request->visit_date,
            'chief_complaint' => $request->chief_complaint,
            'ear_examination' => $request->ear_examination,
            'nose_examination' => $request->nose_examination,
            'throat_examination' => $request->throat_examination,
            'neck_examination' => $request->neck_examination,
            'cranial_nerves' => $request->cranial_nerves,
            'diagnosis' => $request->diagnosis,
            'icd10_code' => $request->icd10_code,
            'treatment_plan' => $request->treatment_plan,
            'medications' => $request->medications,
            'followup_date' => $request->followup_date,
            'notes' => $request->notes,
            'created_by' => $user->id,
        ]);

        return redirect()->route('ent.show', $entRecord)
            ->with('success', 'ENT record created successfully.');
    }

    /**
     * Display the specified ENT record.
     */
    public function show(EntRecord $entRecord)
    {
        $user = Auth::user();

        if ($entRecord->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to ENT record.');
        }

        $entRecord->load(['patient', 'doctor', 'audiometryTests.performer']);

        return view('ent.show', compact('entRecord'));
    }

    /**
     * Show the form for editing the ENT record.
     */
    public function edit(EntRecord $entRecord)
    {
        $user = Auth::user();

        if ($entRecord->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to ENT record.');
        }

        $patients = Patient::where('clinic_id', $user->clinic_id)
            ->orderBy('first_name')
            ->get();

        $doctors = User::where('clinic_id', $user->clinic_id)
            ->whereIn('role', ['doctor', 'admin'])
            ->orderBy('first_name')
            ->get();

        return view('ent.edit', compact('entRecord', 'patients', 'doctors'));
    }

    /**
     * Update the specified ENT record.
     */
    public function update(Request $request, EntRecord $entRecord)
    {
        $user = Auth::user();

        if ($entRecord->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to ENT record.');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'ear_examination' => 'nullable|string',
            'nose_examination' => 'nullable|string',
            'throat_examination' => 'nullable|string',
            'neck_examination' => 'nullable|string',
            'cranial_nerves' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'icd10_code' => 'nullable|string|max:20',
            'treatment_plan' => 'nullable|string',
            'medications' => 'nullable|string',
            'followup_date' => 'nullable|date|after:visit_date',
            'notes' => 'nullable|string',
        ]);

        $entRecord->update([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'visit_date' => $request->visit_date,
            'chief_complaint' => $request->chief_complaint,
            'ear_examination' => $request->ear_examination,
            'nose_examination' => $request->nose_examination,
            'throat_examination' => $request->throat_examination,
            'neck_examination' => $request->neck_examination,
            'cranial_nerves' => $request->cranial_nerves,
            'diagnosis' => $request->diagnosis,
            'icd10_code' => $request->icd10_code,
            'treatment_plan' => $request->treatment_plan,
            'medications' => $request->medications,
            'followup_date' => $request->followup_date,
            'notes' => $request->notes,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('ent.show', $entRecord)
            ->with('success', 'ENT record updated successfully.');
    }

    /**
     * Remove the specified ENT record.
     */
    public function destroy(EntRecord $entRecord)
    {
        $user = Auth::user();

        if ($entRecord->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to ENT record.');
        }

        $entRecord->delete();

        return redirect()->route('ent.index')
            ->with('success', 'ENT record deleted successfully.');
    }
}
