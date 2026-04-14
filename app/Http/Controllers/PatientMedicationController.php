<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\Patient;
use App\Models\PatientMedication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientMedicationController extends Controller
{
    use AuthorizesPatientAccess;

    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatientAccess($patient);

        $validated = $request->validate([
            'medication_name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'indication' => 'nullable|string|max:255',
            'status' => 'required|in:current,past',
            'started_on' => 'nullable|date',
            'ended_on' => 'nullable|date|after_or_equal:started_on',
            'notes' => 'nullable|string',
        ]);

        PatientMedication::create($validated + [
            'patient_id' => $patient->id,
            'clinic_id' => $patient->clinic_id,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('patients.show', $patient)->with('success', __('Medication added successfully.'));
    }
}