<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\DietPlan;
use App\Models\Patient;
use App\Models\PatientNutrition;
use App\Services\PatientProfileModuleRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatientNutritionController extends Controller
{
    use AuthorizesPatientAccess;

    public function show(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, 'nutrition')) {
            abort(404);
        }

        $patient->loadMissing(['clinic', 'nutritionProfile']);
        $patient->loadCount(['dietPlans', 'nutritionProgressMeasurements', 'nutritionGoals']);

        $latestMeasurement = $patient->nutritionProgressMeasurements()->latest('measurement_date')->first();
        $recentMeasurements = $patient->nutritionProgressMeasurements()->latest('measurement_date')->limit(6)->get();
        $activeGoal = $patient->nutritionGoals()->active()->latest()->first();
        $recentDietPlans = $patient->dietPlans()->with('doctor')->latest()->limit(6)->get();
        $recentVisits = $patient->visits()->with(['creator', 'hpi'])->latest('visit_date')->limit(6)->get();
        $latestVisit = $recentVisits->first();

        $nutritionProfile = $patient->nutritionProfile ?: new PatientNutrition([
            'height' => $latestMeasurement?->height_cm ?? $patient->height,
            'weight' => $latestMeasurement?->weight_kg ?? $patient->weight,
            'bmi' => $latestMeasurement?->bmi ?? $patient->bmi,
            'diet_type' => $patient->dietPlans()->latest()->value('goal')
                ? Str::headline(str_replace('_', ' ', (string) $patient->dietPlans()->latest()->value('goal')))
                : null,
        ]);

        $lastVisitLabel = optional($latestVisit?->visit_date)->format('M d, Y') ?: __('Not recorded');

        return view('patients.nutrition.show', compact(
            'patient',
            'nutritionProfile',
            'latestMeasurement',
            'recentMeasurements',
            'activeGoal',
            'recentDietPlans',
            'recentVisits',
            'lastVisitLabel'
        ));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatientAccess($patient);

        if (!PatientProfileModuleRegistry::isAvailableForPatient($patient, 'nutrition')) {
            abort(404);
        }

        $validated = $request->validate([
            'height' => 'nullable|numeric|min:30|max:300',
            'weight' => 'nullable|numeric|min:1|max:500',
            'diet_type' => 'nullable|string|max:255',
            'goals' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $nutritionProfile = PatientNutrition::updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'height' => $validated['height'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'diet_type' => $validated['diet_type'] ?? null,
                'goals' => $validated['goals'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        $patient->update([
            'height' => $nutritionProfile->height,
            'weight' => $nutritionProfile->weight,
            'bmi' => $nutritionProfile->bmi,
        ]);

        return redirect()->route('patients.nutrition.show', ['patient' => $patient->id])
            ->with('success', __('Nutrition summary updated successfully.'));
    }
}