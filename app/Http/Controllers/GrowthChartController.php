<?php

namespace App\Http\Controllers;

use App\Models\GrowthMeasurement;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrowthChartController extends Controller
{
    /**
     * Display a listing of pediatric patients (age <= 20).
     */
    public function patients(Request $request)
    {
        $user = Auth::user();

        $query = Patient::where('is_active', true);

        // Filter by clinic if not super admin
        if (!$user->isSuperAdmin()) {
            $query->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id));
        }

        // Only patients aged 20 and under
        $minDate = now()->subYears(20)->startOfDay();
        $query->where('date_of_birth', '>=', $minDate)
              ->where('date_of_birth', '!=', '0000-00-00');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $patients = $query->withCount('growthMeasurements')
                          ->orderBy('first_name')
                          ->paginate(20)
                          ->appends($request->query());

        return view('pediatric.patients', compact('patients'));
    }

    /**
     * Display the growth chart dashboard for a patient.
     */
    public function index(Patient $patient)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to patient growth charts.');
            }
        }

        // Age validation: growth charts are only for patients aged 20 and under
        if ($patient->age > 20) {
            return redirect()->route('patients.show', $patient)
                ->with('error', __('Pediatric Growth Chart is only available for patients aged 20 years and under. This patient is :age years old.', ['age' => $patient->age]));
        }

        $measurements = $patient->growthMeasurements()
            ->orderBy('measurement_date')
            ->get();

        $gender = strtolower($patient->gender) === 'female' ? 'girls' : 'boys';

        // Determine age range
        $ageMonths = null;
        $rawDob = $patient->getAttributes()['date_of_birth'] ?? $patient->getRawOriginal('date_of_birth');
        if (!empty($rawDob) && $rawDob !== '0000-00-00') {
            try {
                $dob = \Carbon\Carbon::parse($rawDob);
                $ageMonths = $dob->floatDiffInMonths(now());
            } catch (\Exception $e) {
                // skip
            }
        }

        // Get reference data from config
        $chartConfig = config('growth_charts');

        // Corrected age support for LBW / preterm infants
        $isPreterm = $patient->gestational_age_weeks && $patient->gestational_age_weeks < 37;
        $isLBW = $patient->is_low_birth_weight;
        $correctedAgeMonths = $patient->corrected_age_months;
        $weeksPreterm = ($patient->gestational_age_weeks) ? (40 - $patient->gestational_age_weeks) : null;
        $correctionMonths = $weeksPreterm ? round($weeksPreterm * 7 / 30.44, 2) : null;

        return view('pediatric.growth-chart', compact(
            'patient', 'measurements', 'gender', 'ageMonths', 'chartConfig',
            'isPreterm', 'isLBW', 'correctedAgeMonths', 'correctionMonths'
        ));
    }

    /**
     * Store a new growth measurement.
     */
    public function store(Request $request, Patient $patient)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access.');
            }
        }

        $request->validate([
            'measurement_date' => 'required|date',
            'weight_kg' => 'nullable|numeric|min:0|max:300',
            'length_height_cm' => 'nullable|numeric|min:0|max:250',
            'head_circumference_cm' => 'nullable|numeric|min:0|max:80',
            'notes' => 'nullable|string|max:1000',
        ]);

        GrowthMeasurement::create([
            'patient_id' => $patient->id,
            'clinic_id' => $user->clinic_id,
            'measurement_date' => $request->measurement_date,
            'weight_kg' => $request->weight_kg,
            'length_height_cm' => $request->length_height_cm,
            'head_circumference_cm' => $request->head_circumference_cm,
            'notes' => $request->notes,
            'created_by' => $user->id,
        ]);

        return redirect()->route('pediatric.growth-chart', $patient)
            ->with('success', 'Growth measurement recorded successfully.');
    }

    /**
     * Delete a growth measurement.
     */
    public function destroy(Patient $patient, GrowthMeasurement $measurement)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access.');
            }
        }

        if ($measurement->patient_id !== $patient->id) {
            abort(404);
        }

        $measurement->delete();

        return redirect()->route('pediatric.growth-chart', $patient)
            ->with('success', 'Measurement deleted successfully.');
    }

    /**
     * Return chart data as JSON for AJAX requests.
     */
    public function chartData(Patient $patient, Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403);
            }
        }

        $measurements = $patient->growthMeasurements()->orderBy('measurement_date')->get();
        $gender = strtolower($patient->gender) === 'female' ? 'girls' : 'boys';
        $chartType = $request->get('type', 'weight_for_age');
        $ageRange = $request->get('range', '0-24m');

        $config = config('growth_charts');
        $referenceData = $config[$chartType][$gender][$ageRange] ?? [];
        $percentiles = $config['percentiles'];
        $colors = $config['percentile_colors'];

        // Build percentile datasets
        $datasets = [];
        foreach ($percentiles as $i => $p) {
            $datasets[] = [
                'label' => "P{$p}",
                'data' => array_map(fn($row) => ['x' => $row[0], 'y' => $row[$i + 1]], $referenceData),
                'borderColor' => $colors[$p],
                'borderWidth' => $p === 50 ? 2.5 : 1.5,
                'borderDash' => $p === 50 ? [] : [5, 3],
                'fill' => false,
                'pointRadius' => 0,
                'tension' => 0.4,
            ];
        }

        // Build patient data points
        $patientPoints = $measurements->map(function ($m) use ($chartType) {
            $y = match ($chartType) {
                'weight_for_age' => $m->weight_kg,
                'height_for_age' => $m->length_height_cm,
                'head_circumference_for_age' => $m->head_circumference_cm,
                'bmi_for_age' => $m->bmi,
                default => null,
            };
            return $y !== null ? ['x' => (float) $m->age_months, 'y' => (float) $y] : null;
        })->filter()->values();

        $datasets[] = [
            'label' => 'Patient',
            'data' => $patientPoints,
            'borderColor' => '#2980b9',
            'backgroundColor' => '#2980b9',
            'borderWidth' => 2.5,
            'pointRadius' => 5,
            'pointHoverRadius' => 7,
            'fill' => false,
            'tension' => 0.2,
        ];

        return response()->json([
            'datasets' => $datasets,
            'chartType' => $chartType,
            'ageRange' => $ageRange,
            'gender' => $gender,
        ]);
    }
}

