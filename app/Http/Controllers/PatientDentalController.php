<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\Patient;
use App\Models\PatientDental;
use App\Services\PatientProfileModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientDentalController extends Controller
{
    use AuthorizesPatientAccess;

    public function show(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, 'dental')) {
            abort(404);
        }

        $patient->loadMissing(['clinic', 'dentalProfile']);
        $patient->loadCount(['dentalCharts', 'dentalTreatments', 'dentalImages']);

        $dentalProfile = $patient->dentalProfile ?: new PatientDental([
            'smoking_status' => 'unknown',
            'bruxism' => false,
        ]);

        $latestDentalChart = $patient->latest_dental_chart;
        $dentalLastVisitLabel = optional($latestDentalChart?->created_at)->format('M d, Y') ?: __('Not recorded');
        $recentDentalCharts = $patient->dentalCharts()->with('creator')->latest()->limit(5)->get();
        $recentDentalTreatments = $patient->dentalTreatments()->with(['assignedDoctor', 'creator'])->latest()->limit(8)->get();
        $recentVisits = $patient->visits()->with(['creator', 'hpi'])->latest('visit_date')->limit(6)->get();

        return view('patients.dental.show', compact(
            'patient',
            'dentalProfile',
            'latestDentalChart',
            'dentalLastVisitLabel',
            'recentDentalCharts',
            'recentDentalTreatments',
            'recentVisits'
        ));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatientAccess($patient);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, 'dental')) {
            abort(404);
        }

        $validated = $request->validate([
            'oral_hygiene' => 'nullable|in:' . implode(',', array_keys(PatientDental::ORAL_HYGIENE_STATUSES)),
            'smoking_status' => 'nullable|in:' . implode(',', array_keys(PatientDental::SMOKING_STATUSES)),
            'bruxism' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        PatientDental::updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'oral_hygiene' => $validated['oral_hygiene'] ?? null,
                'smoking_status' => $validated['smoking_status'] ?? null,
                'bruxism' => $request->boolean('bruxism'),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->route('patients.dental.show', ['patient' => $patient->id])
            ->with('success', __('Dental profile updated successfully.'));
    }
}