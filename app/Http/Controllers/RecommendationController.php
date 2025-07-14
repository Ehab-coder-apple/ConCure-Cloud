<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\Medicine;
use App\Models\LabRequest;
use App\Models\LabRequestTest;
use App\Models\LabTest;
use App\Models\DietPlan;
use App\Models\DietPlanMeal;
use App\Models\DietPlanMealFood;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RecommendationController extends Controller
{
    /**
     * Display the recommendations dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get recent prescriptions, lab requests, and diet plans
        $clinicFilter = function ($q) use ($user) {
            $q->where('clinic_id', $user->clinic_id);
        };

        $recentPrescriptions = Prescription::with(['patient', 'doctor'])
            ->whereHas('patient', $clinicFilter)
            ->latest()
            ->limit(5)
            ->get();

        $recentLabRequests = LabRequest::with(['patient', 'doctor'])
            ->whereHas('patient', $clinicFilter)
            ->latest()
            ->limit(5)
            ->get();

        $recentDietPlans = DietPlan::with(['patient', 'doctor'])
            ->whereHas('patient', $clinicFilter)
            ->latest()
            ->limit(5)
            ->get();

        return view('recommendations.index', compact(
            'recentPrescriptions',
            'recentLabRequests',
            'recentDietPlans'
        ));
    }

    /**
     * Display lab requests.
     */
    public function labRequests(Request $request)
    {
        $user = auth()->user();

        $query = LabRequest::with(['patient', 'doctor', 'tests']);

        // Filter by clinic for all users
        $query->whereHas('patient', function ($q) use ($user) {
            $q->where('clinic_id', $user->clinic_id);
        });

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('lab_name')) {
            $query->where('lab_name', 'like', "%{$request->lab_name}%");
        }

        if ($request->filled('custom_lab_name')) {
            $query->where('lab_name', 'like', "%{$request->custom_lab_name}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_id', 'like', "%{$search}%");
                  });
            });
        }

        $labRequests = $query->latest()->paginate(15);

        // Get patients for the dropdown
        $patients = \App\Models\Patient::where('clinic_id', $user->clinic_id)
                                      ->select('id', 'patient_id', 'first_name', 'last_name')
                                      ->orderBy('first_name')
                                      ->orderBy('last_name')
                                      ->get();

        // Get external labs for the dropdown
        $externalLabs = \App\Models\ExternalLab::byClinic($user->clinic_id)
                                              ->active()
                                              ->ordered()
                                              ->get();

        // Get unique lab names that have been used in lab requests for filter dropdown
        $usedLabNames = LabRequest::whereHas('patient', function ($q) use ($user) {
                                    $q->where('clinic_id', $user->clinic_id);
                                })
                                ->whereNotNull('lab_name')
                                ->where('lab_name', '!=', '')
                                ->distinct()
                                ->pluck('lab_name')
                                ->sort()
                                ->values();

        return view('recommendations.lab-requests', compact('labRequests', 'patients', 'externalLabs', 'usedLabNames'));
    }

    /**
     * Store a new lab request.
     */
    public function storeLabRequest(Request $request)
    {
        $user = auth()->user();

        // Check if user has permission to create prescriptions (admin-delegated)
        if (!$user->hasPermission('prescriptions_create')) {
            abort(403, 'You do not have permission to create lab requests. Please contact your administrator.');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'clinical_notes' => 'nullable|string',
            'due_date' => 'nullable|date|after:today',
            'priority' => 'required|in:normal,urgent,stat',
            'lab_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tests' => 'required|array|min:1',
            'tests.*.test_name' => 'required|string|max:255',
            'tests.*.instructions' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $user) {
            $labRequest = LabRequest::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $user->id,
                'clinical_notes' => $request->clinical_notes,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'lab_name' => $request->lab_name,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            foreach ($request->tests as $testData) {
                $labRequest->addTest([
                    'lab_test_id' => $testData['lab_test_id'] ?? null,
                    'test_name' => $testData['test_name'],
                    'instructions' => $testData['instructions'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Lab request created successfully.');
    }

    /**
     * Show a specific lab request.
     */
    public function showLabRequest(LabRequest $labRequest)
    {
        $user = auth()->user();

        // Ensure user can only view lab requests from their clinic
        if ($labRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to lab request.');
        }

        $labRequest->load(['patient', 'doctor', 'tests']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'labRequest' => $labRequest
            ]);
        }

        return view('recommendations.lab-request-details', compact('labRequest'));
    }

    /**
     * Print a lab request.
     */
    public function printLabRequest(LabRequest $labRequest)
    {
        $user = auth()->user();

        // Ensure user can only print lab requests from their clinic
        if ($labRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to lab request.');
        }

        $labRequest->load(['patient', 'doctor', 'tests']);

        return view('recommendations.lab-request-print', compact('labRequest'));
    }

    /**
     * Update lab request status.
     */
    public function updateLabRequestStatus(Request $request, LabRequest $labRequest)
    {
        $user = auth()->user();

        // Ensure user can only update lab requests from their clinic
        if ($labRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to lab request.');
        }

        $request->validate([
            'status' => 'required|in:pending,completed,cancelled'
        ]);

        $labRequest->update([
            'status' => $request->status
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lab request status updated successfully.',
                'labRequest' => $labRequest
            ]);
        }

        return back()->with('success', 'Lab request status updated successfully.');
    }

    /**
     * Display prescriptions.
     */
    public function prescriptions(Request $request)
    {
        $user = auth()->user();
        
        $query = Prescription::with(['patient', 'doctor', 'medicines']);

        // Filter by clinic for all users
        $query->whereHas('patient', function ($q) use ($user) {
            $q->where('clinic_id', $user->clinic_id);
        });

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_id', 'like', "%{$search}%");
                  });
            });
        }

        $prescriptions = $query->latest()->paginate(15);

        return view('recommendations.prescriptions', compact('prescriptions'));
    }

    /**
     * Store a new prescription.
     */
    public function storePrescription(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canPrescribe()) {
            abort(403, 'Only doctors can create prescriptions.');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'medicines' => 'required|array|min:1',
            'medicines.*.medicine_id' => 'nullable|exists:medicines,id',
            'medicines.*.medicine_name' => 'required|string|max:255',
            'medicines.*.dosage' => 'required|string|max:255',
            'medicines.*.frequency' => 'required|string|max:255',
            'medicines.*.duration' => 'required|string|max:255',
            'medicines.*.instructions' => 'nullable|string',
            'medicines.*.quantity' => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $user) {
            $prescription = Prescription::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $user->id,
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes,
                'status' => 'active',
            ]);

            foreach ($request->medicines as $medicineData) {
                $prescription->addMedicine([
                    'medicine_id' => $medicineData['medicine_id'],
                    'medicine_name' => $medicineData['medicine_name'],
                    'dosage' => $medicineData['dosage'],
                    'frequency' => $medicineData['frequency'],
                    'duration' => $medicineData['duration'],
                    'instructions' => $medicineData['instructions'] ?? null,
                    'quantity' => $medicineData['quantity'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Prescription created successfully.');
    }

    /**
     * Display diet plans.
     */
    public function dietPlans(Request $request)
    {
        $user = auth()->user();
        
        $query = DietPlan::with(['patient', 'doctor']);

        // Filter by clinic for all users
        $query->whereHas('patient', function ($q) use ($user) {
            $q->where('clinic_id', $user->clinic_id);
        });

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('goal')) {
            $query->byGoal($request->goal);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plan_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_id', 'like', "%{$search}%");
                  });
            });
        }

        $dietPlans = $query->latest()->paginate(15);

        return view('recommendations.diet-plans', compact('dietPlans'));
    }

    /**
     * Store a new diet plan.
     */
    public function storeDietPlan(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canPrescribe()) {
            abort(403, 'Only doctors can create diet plans.');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal' => 'required|in:weight_loss,weight_gain,maintenance,muscle_gain,health_improvement,other',
            'goal_description' => 'nullable|string',
            'duration_days' => 'nullable|integer|min:1|max:365',
            'target_calories' => 'nullable|numeric|min:500|max:5000',
            'target_protein' => 'nullable|numeric|min:0|max:500',
            'target_carbs' => 'nullable|numeric|min:0|max:1000',
            'target_fat' => 'nullable|numeric|min:0|max:300',
            'instructions' => 'nullable|string',
            'restrictions' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $dietPlan = DietPlan::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'goal' => $request->goal,
            'goal_description' => $request->goal_description,
            'duration_days' => $request->duration_days,
            'target_calories' => $request->target_calories,
            'target_protein' => $request->target_protein,
            'target_carbs' => $request->target_carbs,
            'target_fat' => $request->target_fat,
            'instructions' => $request->instructions,
            'restrictions' => $request->restrictions,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'active',
        ]);

        return back()->with('success', 'Diet plan created successfully.');
    }

    /**
     * Generate PDF for diet plan.
     */
    public function generateDietPlanPDF(DietPlan $dietPlan)
    {
        $user = auth()->user();
        
        // Check access
        if (
            $dietPlan->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to diet plan.');
        }

        $dietPlan->load(['patient', 'doctor', 'meals.foods.food']);

        $pdf = Pdf::loadView('recommendations.diet-plan-pdf', compact('dietPlan'));
        
        return $pdf->download("diet-plan-{$dietPlan->plan_number}.pdf");
    }
}
