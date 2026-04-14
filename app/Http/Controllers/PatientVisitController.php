<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\VisitHpi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientVisitController extends Controller
{
    use AuthorizesPatientAccess;

    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatientAccess($patient);

        $validated = $request->validate([
            'visit_date' => 'required|date',
            'visit_type' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'reason_for_visit' => 'nullable|string',
            'notes' => 'nullable|string',
            'chief_complaint' => 'nullable|string',
            'hpi_summary' => 'nullable|string',
            'associated_symptoms' => 'nullable|string',
            'clinical_notes' => 'nullable|string',
        ]);

        $visit = PatientVisit::create([
            'patient_id' => $patient->id,
            'clinic_id' => $patient->clinic_id,
            'recorded_by' => auth()->id(),
            'visit_date' => $validated['visit_date'],
            'visit_type' => $validated['visit_type'] ?? 'consultation',
            'status' => $validated['status'] ?? 'completed',
            'reason_for_visit' => $validated['reason_for_visit'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->filled('chief_complaint') || $request->filled('hpi_summary') || $request->filled('associated_symptoms') || $request->filled('clinical_notes')) {
            VisitHpi::create([
                'visit_id' => $visit->id,
                'chief_complaint' => $validated['chief_complaint'] ?? null,
                'hpi_summary' => $validated['hpi_summary'] ?? null,
                'associated_symptoms' => $validated['associated_symptoms'] ?? null,
                'clinical_notes' => $validated['clinical_notes'] ?? null,
            ]);
        }

        return redirect()->route('patients.visits.show', [$patient, $visit])->with('success', __('Visit and HPI saved successfully.'));
    }

    public function show(Patient $patient, PatientVisit $visit)
    {
        $this->authorizePatientAccess($patient);

        if ($visit->patient_id !== $patient->id) {
            abort(404);
        }

        $visit->load(['hpi', 'creator']);

        return view('patients.visits.show', compact('patient', 'visit'));
    }
}