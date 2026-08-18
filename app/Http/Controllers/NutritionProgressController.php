<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\NutritionGoal;
use App\Models\PatientNutrition;
use App\Models\NutritionProgressMeasurement;
use Illuminate\Http\Request;

class NutritionProgressController extends Controller
{
    /**
     * Display the progress dashboard with patient selector.
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        $patientsQuery = Patient::where('clinic_id', $user->clinic_id)->active();
        if ($user->role === 'doctor') {
            $patientsQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('dietPlans', fn($dq) => $dq->where('doctor_id', $user->id));
            });
        }
        $patients = $patientsQuery->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'patient_id', 'gender', 'date_of_birth', 'height', 'weight']);

        $selectedPatient = null;
        $measurements = collect();
        $goal = null;

        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::where('clinic_id', $user->clinic_id)->findOrFail($request->patient_id);
            $measurements = $selectedPatient->nutritionProgressMeasurements()->orderBy('measurement_date')->get();
            $goal = $selectedPatient->nutritionGoals()->active()->first();
        }

        return view('nutrition.progress-dashboard', compact('patients', 'selectedPatient', 'measurements', 'goal'));
    }

    /**
     * Store a new measurement.
     */
    public function storeMeasurement(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'measurement_date' => 'required|date',
            'weight_kg' => 'nullable|numeric|min:1|max:500',
            'height_cm' => 'nullable|numeric|min:30|max:300',
            'fat_kg' => 'nullable|numeric|min:0|max:300',
            'muscle_kg' => 'nullable|numeric|min:0|max:300',
            'waist_cm' => 'nullable|numeric|min:30|max:250',
            'hip_cm' => 'nullable|numeric|min:30|max:250',
            'whr_direct' => 'nullable|numeric|min:0.3|max:2',
            'visceral_fat' => 'nullable|numeric|min:1|max:60',
            'mineral_kg' => 'nullable|numeric|min:0|max:20',
            'body_water_liters' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $patient = Patient::where('clinic_id', $user->clinic_id)->findOrFail($request->patient_id);

        $measurement = NutritionProgressMeasurement::create([
            'patient_id' => $patient->id,
            'clinic_id' => $user->clinic_id,
            'recorded_by' => $user->id,
            'measurement_date' => $request->measurement_date,
            'weight_kg' => $request->weight_kg,
            'height_cm' => $request->height_cm ?? $patient->height,
            'fat_kg' => $request->fat_kg,
            'muscle_kg' => $request->muscle_kg,
            'waist_cm' => $request->waist_cm,
            'hip_cm' => $request->hip_cm,
            'whr_direct' => $request->whr_direct,
            'visceral_fat' => $request->visceral_fat,
            'mineral_kg' => $request->mineral_kg,
            'body_water_liters' => $request->body_water_liters,
            'notes' => $request->notes,
        ]);

        PatientNutrition::updateOrCreate(
            ['patient_id' => $patient->id],
            [
                'height' => $measurement->height_cm ?? $patient->height,
                'weight' => $measurement->weight_kg ?? $patient->weight,
                'bmi' => $measurement->bmi,
            ]
        );

        $patient->update([
            'height' => $measurement->height_cm ?? $patient->height,
            'weight' => $measurement->weight_kg ?? $patient->weight,
            'bmi' => $measurement->bmi ?? $patient->bmi,
        ]);

        return redirect()->route('nutrition.progress.dashboard', ['patient_id' => $patient->id])
            ->with('success', __('Measurement recorded successfully.'));
    }

    /**
     * Store or update patient goal.
     */
    public function storeGoal(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'target_weight' => 'nullable|numeric|min:20|max:500',
            'target_fat_kg' => 'nullable|numeric|min:0|max:300',
            'target_muscle_kg' => 'nullable|numeric|min:0|max:300',
            'target_bmi' => 'nullable|numeric|min:15|max:50',
            'target_waist_cm' => 'nullable|numeric|min:30|max:200',
            'target_hip_cm' => 'nullable|numeric|min:30|max:200',
            'target_whr' => 'nullable|numeric|min:0.3|max:2',
            'target_visceral_fat' => 'nullable|numeric|min:1|max:30',
            'target_mineral_kg' => 'nullable|numeric|min:0|max:20',
            'target_body_water_liters' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $patient = Patient::where('clinic_id', $user->clinic_id)->findOrFail($request->patient_id);

        // Deactivate previous goals
        NutritionGoal::where('patient_id', $patient->id)->update(['is_active' => false]);

        NutritionGoal::create([
            'patient_id' => $patient->id,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'target_weight' => $request->target_weight,
            'target_fat_kg' => $request->target_fat_kg,
            'target_muscle_kg' => $request->target_muscle_kg,
            'target_bmi' => $request->target_bmi,
            'target_waist_cm' => $request->target_waist_cm,
            'target_hip_cm' => $request->target_hip_cm,
            'target_whr' => $request->target_whr,
            'target_visceral_fat' => $request->target_visceral_fat,
            'target_mineral_kg' => $request->target_mineral_kg,
            'target_body_water_liters' => $request->target_body_water_liters,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        return redirect()->route('nutrition.progress.dashboard', ['patient_id' => $patient->id])
            ->with('success', __('Goal saved successfully.'));
    }

    /**
     * Return chart data as JSON for AJAX.
     */
    public function chartData(Request $request)
    {
        $user = auth()->user();
        $patient = Patient::where('clinic_id', $user->clinic_id)->findOrFail($request->patient_id);

        $measurements = $patient->nutritionProgressMeasurements()->orderBy('measurement_date')->get();
        $goal = $patient->nutritionGoals()->active()->first();

        $labels = $measurements->pluck('measurement_date')->map(fn($d) => $d->format('Y-m-d'))->toArray();

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                'weight' => $measurements->pluck('weight_kg')->toArray(),
                'bmi' => $measurements->pluck('bmi')->toArray(),
                'fat_kg' => $measurements->pluck('fat_kg')->toArray(),
                'muscle_kg' => $measurements->pluck('muscle_kg')->toArray(),
                'waist_to_hip_ratio' => $measurements->pluck('waist_to_hip_ratio')->toArray(),
                'visceral_fat' => $measurements->pluck('visceral_fat')->toArray(),
                'mineral_kg' => $measurements->pluck('mineral_kg')->toArray(),
                'body_water_liters' => $measurements->pluck('body_water_liters')->toArray(),
            ],
            'goal' => $goal ? [
                'weight' => $goal->target_weight,
                'fat_kg' => $goal->target_fat_kg,
                'muscle_kg' => $goal->target_muscle_kg,
                'bmi' => $goal->target_bmi,
                'visceral_fat' => $goal->target_visceral_fat,
                'mineral_kg' => $goal->target_mineral_kg,
                'body_water_liters' => $goal->target_body_water_liters,
            ] : null,
            'reference_ranges' => $this->getReferenceRanges($patient),
        ];

        // Calculate weight-to-goal %
        if ($goal && $goal->target_weight && $measurements->count() > 0) {
            $firstWeight = $measurements->first()->weight_kg;
            $chartData['datasets']['weight_to_goal'] = $measurements->map(function ($m) use ($firstWeight, $goal) {
                if (!$m->weight_kg || !$firstWeight) return null;
                $totalChange = abs($goal->target_weight - $firstWeight);
                if ($totalChange == 0) return 100;
                $currentChange = abs($m->weight_kg - $firstWeight);
                return round(min(($currentChange / $totalChange) * 100, 100), 1);
            })->toArray();
        }

        return response()->json($chartData);
    }

    /**
     * Delete a measurement.
     */
    public function destroyMeasurement(NutritionProgressMeasurement $measurement)
    {
        $user = auth()->user();
        if ($measurement->clinic_id !== $user->clinic_id) {
            abort(403);
        }
        $patientId = $measurement->patient_id;
        $measurement->delete();

        return redirect()->route('nutrition.progress.dashboard', ['patient_id' => $patientId])
            ->with('success', __('Measurement deleted.'));
    }

    /**
     * Get reference ranges based on patient age & gender.
     */
    private function getReferenceRanges(Patient $patient): array
    {
        $gender = $patient->gender ?? 'male';
        $age = $patient->age ?? 30;

        return [
            'bmi' => ['min' => 18.5, 'max' => 24.9, 'label' => 'Normal BMI'],
            'waist_to_hip_ratio' => $gender === 'male'
                ? ['min' => 0.85, 'max' => 0.95, 'label' => 'Normal WHR (Male)']
                : ['min' => 0.75, 'max' => 0.85, 'label' => 'Normal WHR (Female)'],
            'visceral_fat' => ['min' => 1, 'max' => 12, 'label' => 'Healthy Visceral Fat'],
        ];
    }
}

