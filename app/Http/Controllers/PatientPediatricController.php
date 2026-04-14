<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\Patient;
use App\Models\PatientPediatric;
use App\Services\PatientProfileModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientPediatricController extends Controller
{
    use AuthorizesPatientAccess;

    public function show(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, 'pediatric')) {
            abort(404);
        }

        $patient->loadMissing(['clinic', 'pediatricProfile', 'vaccinationSchedule']);
        $patient->loadCount(['growthMeasurements', 'vaccinations', 'pediatricPrescriptions']);

        $pediatricProfile = $patient->pediatricProfile ?: new PatientPediatric([
            'birth_weight' => $patient->getRawOriginal('birth_weight'),
            'gestational_age' => $patient->getRawOriginal('gestational_age_weeks'),
            'vaccination_status' => 'unknown',
            'feeding_type' => 'unknown',
        ]);

        $latestGrowthMeasurement = $patient->growthMeasurements()->latest('measurement_date')->first();
        $recentGrowthMeasurements = $patient->growthMeasurements()->latest('measurement_date')->limit(6)->get();
        $recentVaccinations = $patient->vaccinations()->with('vaccine')->latest('scheduled_date')->limit(8)->get();

        return view('patients.pediatric.show', compact(
            'patient',
            'pediatricProfile',
            'latestGrowthMeasurement',
            'recentGrowthMeasurements',
            'recentVaccinations'
        ));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatientAccess($patient);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, 'pediatric')) {
            abort(404);
        }

        $validated = $request->validate([
            'birth_weight' => 'nullable|numeric|min:0|max:10000',
            'gestational_age' => 'nullable|integer|min:20|max:45',
            'feeding_type' => 'nullable|in:' . implode(',', array_keys(PatientPediatric::FEEDING_TYPES)),
            'vaccination_status' => 'nullable|in:' . implode(',', array_keys(PatientPediatric::VACCINATION_STATUSES)),
            'notes' => 'nullable|string',
        ]);

        PatientPediatric::updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'birth_weight' => $validated['birth_weight'] ?? null,
                'gestational_age' => $validated['gestational_age'] ?? null,
                'feeding_type' => $validated['feeding_type'] ?? null,
                'vaccination_status' => $validated['vaccination_status'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->route('patients.pediatric.show', ['patient' => $patient->id])
            ->with('success', __('Pediatric profile updated successfully.'));
    }
}