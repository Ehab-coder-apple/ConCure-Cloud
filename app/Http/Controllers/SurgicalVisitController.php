<?php

namespace App\Http\Controllers;

use App\Models\SurgicalCase;
use App\Models\SurgicalVisit;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SurgicalVisitController extends Controller
{
    /**
     * Show form to record a surgical visit for a case.
     */
    public function create(Request $request, SurgicalCase $surgicalCase)
    {
        $user = Auth::user();

        if ($user->clinic_id && $surgicalCase->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        $surgicalCase->load(['patient']);

        // Calculate the next visit number based on existing visits
        $visitCount = $surgicalCase->visits()->count();
        $visitNumber = $visitCount + 1;
        $patient = $surgicalCase->patient;

        return view('surgery.visits.create', compact('surgicalCase', 'patient', 'visitNumber'));
    }

    /**
     * Store a new surgical visit (post-op follow-up).
     */
    public function store(Request $request, SurgicalCase $surgicalCase)
    {
        $user = Auth::user();

        if ($user->clinic_id && $surgicalCase->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        $validated = $request->validate([
            'visit_date' => 'required|date',
            'clinical_observations' => 'nullable|string',
            'wound_status' => 'nullable|string',
            'wound_assessment' => 'nullable|array',
            'medications_prescribed' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $user, $surgicalCase) {
            // Calculate the next visit number based on existing visits
            $visitCount = $surgicalCase->visits()->count();
            $visitNumber = $visitCount + 1;

            SurgicalVisit::create([
                'clinic_id' => $surgicalCase->clinic_id,
                'patient_id' => $surgicalCase->patient_id,
                'surgical_case_id' => $surgicalCase->id,
                'visit_date' => $validated['visit_date'],
                'visit_number' => $visitNumber,
                'clinical_observations' => $validated['clinical_observations'] ?? null,
                'wound_status' => $validated['wound_status'] ?? null,
                'wound_assessment' => $validated['wound_assessment'] ?? null,
                'medications_prescribed' => $validated['medications_prescribed'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
            ]);
        });

        return redirect()->route('surgery.show', $surgicalCase)
            ->with('success', 'Surgical visit recorded successfully.');
    }

    /**
     * Show form to edit an existing surgical visit (follow-up).
     */
    public function edit(SurgicalCase $surgicalCase, SurgicalVisit $visit)
    {
        $user = Auth::user();

        if ($user->clinic_id && $surgicalCase->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        if ((int) $visit->surgical_case_id !== $surgicalCase->id) {
            abort(404);
        }

        $surgicalCase->load('patient');
        $patient = $surgicalCase->patient;
        $visitNumber = $visit->visit_number;

        // Pre-fill the shared create/edit form (built around old()) with the
        // visit's existing values without duplicating the view.
        session()->flash('_old_input', [
            'visit_date' => optional($visit->visit_date)->format('Y-m-d'),
            'wound_status' => $visit->wound_status,
            'clinical_observations' => $visit->clinical_observations,
            'medications_prescribed' => $visit->medications_prescribed,
            'notes' => $visit->notes,
            'wound_assessment' => $visit->wound_assessment ?? [],
        ]);

        return view('surgery.visits.create', compact('surgicalCase', 'patient', 'visitNumber', 'visit'));
    }

    /**
     * Update an existing surgical visit (follow-up).
     */
    public function update(Request $request, SurgicalCase $surgicalCase, SurgicalVisit $visit)
    {
        $user = Auth::user();

        if ($user->clinic_id && $surgicalCase->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        if ((int) $visit->surgical_case_id !== $surgicalCase->id) {
            abort(404);
        }

        $validated = $request->validate([
            'visit_date' => 'required|date',
            'clinical_observations' => 'nullable|string',
            'wound_status' => 'nullable|string',
            'wound_assessment' => 'nullable|array',
            'medications_prescribed' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $visit->update([
            'visit_date' => $validated['visit_date'],
            'clinical_observations' => $validated['clinical_observations'] ?? null,
            'wound_status' => $validated['wound_status'] ?? null,
            'wound_assessment' => $validated['wound_assessment'] ?? null,
            'medications_prescribed' => $validated['medications_prescribed'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('surgery.show', $surgicalCase)
            ->with('success', 'Surgical visit updated successfully.');
    }
}
