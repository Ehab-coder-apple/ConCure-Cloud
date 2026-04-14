<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\Patient;
use App\Models\PatientMedicalOverview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientMedicalOverviewController extends Controller
{
    use AuthorizesPatientAccess;

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatientAccess($patient);

        $validated = $request->validate([
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'surgeries' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'current_medications_summary' => 'nullable|string',
            'flags' => 'nullable|array',
            'flags.*' => 'nullable|boolean',
        ]);

        $flags = collect($request->input('flags', []))
            ->map(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN))
            ->filter()
            ->all();

        PatientMedicalOverview::updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'allergies' => $validated['allergies'] ?? null,
                'chronic_diseases' => $validated['chronic_diseases'] ?? null,
                'surgeries' => $validated['surgeries'] ?? null,
                'medical_history' => $validated['medical_history'] ?? null,
                'current_medications_summary' => $validated['current_medications_summary'] ?? null,
                'flags' => $flags,
            ]
        );

        $patient->forceFill([
            'is_pregnant' => !empty($flags['pregnant']),
        ])->save();

        return redirect()->route('patients.show', $patient)->with('success', __('Medical overview updated successfully.'));
    }
}