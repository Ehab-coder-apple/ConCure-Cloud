<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\Patient;
use App\Models\PatientModule;
use App\Services\PatientProfileModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientModuleController extends Controller
{
    use AuthorizesPatientAccess;

    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatientAccess($patient);

        $validated = $request->validate([
            'module_name' => 'required|string|max:100',
        ]);

        $moduleName = $validated['module_name'];

        $this->authorizeModuleSectionAccess($moduleName);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, $moduleName)) {
            return redirect()->route('patients.show', $patient)->with('error', __('This module is not available for the selected patient.'));
        }

        PatientModule::updateOrCreate(
            ['patient_id' => $patient->id, 'module_name' => $moduleName],
            ['is_active' => true]
        );

        return redirect()->route('patients.show', $patient)->with('success', __('Module activated successfully.'));
    }

    public function destroy(Patient $patient, string $module): RedirectResponse
    {
        $this->authorizePatientAccess($patient);
        $this->authorizeModuleSectionAccess($module);

        PatientModule::query()
            ->where('patient_id', $patient->id)
            ->where('module_name', $module)
            ->update(['is_active' => false]);

        return redirect()->route('patients.show', $patient)->with('success', __('Module hidden from the patient profile.'));
    }

    public function show(Patient $patient, string $module)
    {
        $this->authorizePatientAccess($patient);
        $this->authorizeModuleSectionAccess($module);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, $module)) {
            abort(404);
        }

        if ($module === 'dental') {
            return redirect()->route('patients.dental.show', ['patient' => $patient->id]);
        }

        if ($module === 'orthodontics') {
            return redirect()->route('orthodontics.index', ['patient_id' => $patient->id]);
        }

        if ($module === 'ent') {
            return redirect()->route('patients.ent.show', ['patient' => $patient->id]);
        }

        if ($module === 'pediatric') {
            return redirect()->route('patients.pediatric.show', ['patient' => $patient->id]);
        }

        if ($module === 'nutrition') {
            return redirect()->route('patients.nutrition.show', ['patient' => $patient->id]);
        }

        $moduleDefinition = PatientProfileModuleRegistry::find($module);
        if (!$moduleDefinition) {
            abort(404);
        }

        $moduleRecord = $patient->modules()->where('module_name', $module)->first();
        if (!$moduleRecord || !$moduleRecord->is_active) {
            abort(404);
        }

        $patient->loadCount([
            'dentalCharts',
            'dentalTreatments',
            'dentalImages',
            'growthMeasurements',
            'pediatricPrescriptions',
            'vaccinations',
            'dietPlans',
            'nutritionProgressMeasurements',
            'nutritionGoals',
        ]);

        $moduleStats = PatientProfileModuleRegistry::summaryStats($patient, $module);
        $moduleLinks = PatientProfileModuleRegistry::moduleLinks($patient, $module);

        return view('patients.modules.show', compact('patient', 'module', 'moduleDefinition', 'moduleRecord', 'moduleStats', 'moduleLinks'));
    }

    private function authorizeModuleSectionAccess(string $module): void
    {
        if (!PatientProfileModuleRegistry::isVisibleToUser(auth()->user(), $module)) {
            abort(403, 'Access denied. Insufficient permissions for this module.');
        }
    }
}