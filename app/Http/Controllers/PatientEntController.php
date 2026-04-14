<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\Patient;
use App\Models\PatientEnt;
use App\Models\PatientFile;
use App\Services\PatientProfileModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientEntController extends Controller
{
    use AuthorizesPatientAccess;

    public function show(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, 'ent')) {
            abort(404);
        }

        $patient->loadMissing(['clinic', 'entProfile']);

        $entProfile = $patient->entProfile ?: new PatientEnt([
            'dizziness' => false,
        ]);

        $recentVisits = $patient->visits()->with(['creator', 'hpi'])->latest('visit_date')->limit(6)->get();
        $entFiles = $patient->files()->with('uploader')->entRelated()->latest()->limit(8)->get();
        $visitContextCount = $patient->visits()->count();
        $entFileCount = $patient->files()->entRelated()->count();

        // Get all audiometry tests for this patient
        $audiometryTests = \App\Models\AudiometryTest::where('patient_id', $patient->id)
            ->with(['performer', 'entRecord'])
            ->latest('test_date')
            ->get();

        return view('patients.ent.show', compact(
            'patient',
            'entProfile',
            'recentVisits',
            'entFiles',
            'visitContextCount',
            'entFileCount',
            'audiometryTests'
        ));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatientAccess($patient);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, 'ent')) {
            abort(404);
        }

        $validated = $request->validate([
            'hearing_issues' => 'nullable|string',
            'nasal_issues' => 'nullable|string',
            'throat_issues' => 'nullable|string',
            'dizziness' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        PatientEnt::updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'hearing_issues' => $validated['hearing_issues'] ?? null,
                'nasal_issues' => $validated['nasal_issues'] ?? null,
                'throat_issues' => $validated['throat_issues'] ?? null,
                'dizziness' => $request->boolean('dizziness'),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->route('patients.ent.show', ['patient' => $patient->id])
            ->with('success', __('ENT profile updated successfully.'));
    }
}