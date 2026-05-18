<?php

namespace App\Http\Controllers;

use App\Models\OrthodonticCase;
use App\Models\OrthodonticVisit;
use App\Models\OrthodonticPhoto;
use App\Models\OrthodonticPayment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrthodonticCaseController extends Controller
{
    /**
     * Display a listing of orthodontic cases.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = OrthodonticCase::with(['patient', 'doctor'])
            ->where('clinic_id', $user->clinic_id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('patient_id', 'like', "%{$search}%");
                  });
            });
        }

        $cases = $query->paginate(20);

        // Get stats
        $stats = [
            'total' => OrthodonticCase::where('clinic_id', $user->clinic_id)->count(),
            'active' => OrthodonticCase::where('clinic_id', $user->clinic_id)->where('status', 'active')->count(),
            'completed' => OrthodonticCase::where('clinic_id', $user->clinic_id)->where('status', 'completed')->count(),
        ];

        return view('orthodontics.index', compact('cases', 'stats'));
    }

    /**
     * Show the form for creating a new case.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Get patients
        $patients = Patient::where('clinic_id', $user->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        // Get doctors
        $doctors = User::whereIn('role', ['doctor', 'admin', 'dental_dept'])
            ->where('clinic_id', $user->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        // Get clinic currency
        $clinicCurrency = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD';

        $preselectedPatientId = $request->get('patient_id');

        return view('orthodontics.create', compact('patients', 'doctors', 'clinicCurrency', 'preselectedPatientId'));
    }

    /**
     * Store a newly created case.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $clinicCurrency = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'currency')
            ->value('value') ?? 'USD';

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'treatment_type' => 'required|string',
            'diagnosis' => 'nullable|string',
            'malocclusion_class' => 'nullable|string',
            'treatment_objectives' => 'nullable|string',
            'start_date' => 'required|date',
            'estimated_duration_months' => 'required|integer|min:1|max:60',
            'current_phase' => 'nullable|string',
            'total_cost' => 'required|numeric|min:0',
            'payment_plan' => 'required|string',
            'notes' => 'nullable|string',
            // Clinical Assessment Fields
            'skeletal_class' => 'nullable|string',
            'overjet' => 'nullable|numeric|min:0|max:99.99',
            'overbite' => 'nullable|numeric|min:0|max:99.99',
            'midline' => 'nullable|string',
            'crowding' => 'nullable|string',
            'crossbite' => 'nullable|string',
            'open_bite' => 'nullable|numeric|min:0|max:99.99',
        ]);

        try {
            DB::beginTransaction();

            $case = OrthodonticCase::create([
                'patient_id' => $request->patient_id,
                'clinic_id' => $user->clinic_id,
                'doctor_id' => $request->doctor_id,
                'treatment_type' => $request->treatment_type,
                'diagnosis' => $request->diagnosis,
                'malocclusion_class' => $request->malocclusion_class,
                'treatment_objectives' => $request->treatment_objectives,
                'start_date' => $request->start_date,
                'estimated_duration_months' => $request->estimated_duration_months,
                'current_phase' => $request->current_phase ?? 'initial',
                'status' => 'active',
                'total_cost' => $request->total_cost,
                'currency' => $clinicCurrency,
                'paid_amount' => 0,
                'payment_plan' => $request->payment_plan,
                'notes' => $request->notes,
                'created_by' => $user->id,
                // Clinical Assessment Fields
                'skeletal_class' => $request->skeletal_class,
                'overjet' => $request->overjet,
                'overbite' => $request->overbite,
                'midline' => $request->midline,
                'crowding' => $request->crowding,
                'crossbite' => $request->crossbite,
                'open_bite' => $request->open_bite,
            ]);

            DB::commit();

            return redirect()->route('orthodontics.show', $case)
                ->with('success', 'Orthodontic case created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create case. Please try again.');
        }
    }

    /**
     * Display the specified case.
     */
    public function show(OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to this case.');
        }

        $orthodonticCase->load([
            'patient',
            'doctor',
            'visits' => function($q) {
                $q->orderBy('visit_date', 'desc')->limit(10);
            },
            'photos' => function($q) {
                $q->orderBy('photo_date', 'desc')->limit(12);
            },
            'payments' => function($q) {
                $q->orderBy('payment_date', 'desc');
            }
        ]);

        return view('orthodontics.show', compact('orthodonticCase'));
    }

    /**
     * Show the form for editing the case.
     */
    public function edit(OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to this case.');
        }

        // Get doctors
        $doctors = User::whereIn('role', ['doctor', 'admin', 'dental_dept'])
            ->where('clinic_id', $user->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('orthodontics.edit', compact('orthodonticCase', 'doctors'));
    }

    /**
     * Update the specified case.
     */
    public function update(Request $request, OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to this case.');
        }

        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'treatment_type' => 'required|string',
            'diagnosis' => 'nullable|string',
            'malocclusion_class' => 'nullable|string',
            'treatment_objectives' => 'nullable|string',
            'estimated_duration_months' => 'required|integer|min:1|max:60',
            'current_phase' => 'nullable|string',
            'status' => 'required|string',
            'total_cost' => 'required|numeric|min:0',
            'payment_plan' => 'required|string',
            'notes' => 'nullable|string',
            // Clinical Assessment Fields
            'skeletal_class' => 'nullable|string',
            'overjet' => 'nullable|numeric|min:0|max:99.99',
            'overbite' => 'nullable|numeric|min:0|max:99.99',
            'midline' => 'nullable|string',
            'crowding' => 'nullable|string',
            'crossbite' => 'nullable|string',
            'open_bite' => 'nullable|numeric|min:0|max:99.99',
        ]);

        try {
            $orthodonticCase->update([
                'doctor_id' => $request->doctor_id,
                'treatment_type' => $request->treatment_type,
                'diagnosis' => $request->diagnosis,
                'malocclusion_class' => $request->malocclusion_class,
                'treatment_objectives' => $request->treatment_objectives,
                'estimated_duration_months' => $request->estimated_duration_months,
                'current_phase' => $request->current_phase,
                'status' => $request->status,
                'total_cost' => $request->total_cost,
                'payment_plan' => $request->payment_plan,
                'notes' => $request->notes,
                'updated_by' => $user->id,
                // Clinical Assessment Fields
                'skeletal_class' => $request->skeletal_class,
                'overjet' => $request->overjet,
                'overbite' => $request->overbite,
                'midline' => $request->midline,
                'crowding' => $request->crowding,
                'crossbite' => $request->crossbite,
                'open_bite' => $request->open_bite,
            ]);

            return redirect()->route('orthodontics.show', $orthodonticCase)
                ->with('success', 'Case updated successfully!');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update case. Please try again.');
        }
    }

    /**
     * Remove the specified case.
     */
    public function destroy(OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to this case.');
        }

        try {
            $orthodonticCase->delete();

            return redirect()->route('orthodontics.index')
                ->with('success', 'Case deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete case. Please try again.');
        }
    }

    /**
     * Store a new visit for the case.
     */
    public function storeVisit(Request $request, OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to this case.');
        }

        $request->validate([
            'visit_date' => 'required|date',
            'visit_type' => 'required|string',
            'procedures_performed' => 'nullable|string',
            'observations' => 'nullable|string',
            'patient_concerns' => 'nullable|string',
            'oral_hygiene_status' => 'nullable|string',
            'broken_brackets' => 'nullable|boolean',
            'appliance_condition' => 'nullable|string',
            'next_appointment_date' => 'nullable|date|after:visit_date',
            'instructions_given' => 'nullable|string',
            'notes' => 'nullable|string',
            // Clinical Mechanics Fields
            'upper_wire' => 'nullable|string|max:255',
            'lower_wire' => 'nullable|string|max:255',
            'elastic_type' => 'nullable|string|max:255',
            'power_chain' => 'nullable|string|max:255',
            'coil_spring' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Get next visit number
            $visitNumber = $orthodonticCase->visits()->max('visit_number') + 1;

            $visit = OrthodonticVisit::create([
                'orthodontic_case_id' => $orthodonticCase->id,
                'patient_id' => $orthodonticCase->patient_id,
                'clinic_id' => $orthodonticCase->clinic_id,
                'doctor_id' => $request->doctor_id ?? $user->id,
                'visit_date' => $request->visit_date,
                'visit_number' => $visitNumber,
                'visit_type' => $request->visit_type,
                'procedures_performed' => $request->procedures_performed,
                'observations' => $request->observations,
                'patient_concerns' => $request->patient_concerns,
                'oral_hygiene_status' => $request->oral_hygiene_status,
                'broken_brackets' => $request->broken_brackets ?? false,
                'appliance_condition' => $request->appliance_condition,
                'next_appointment_date' => $request->next_appointment_date,
                'instructions_given' => $request->instructions_given,
                'notes' => $request->notes,
                'created_by' => $user->id,
                // Clinical Mechanics Fields
                'upper_wire' => $request->upper_wire,
                'lower_wire' => $request->lower_wire,
                'elastic_type' => $request->elastic_type,
                'power_chain' => $request->power_chain,
                'coil_spring' => $request->coil_spring,
            ]);

            DB::commit();

            return redirect()->route('orthodontics.show', $orthodonticCase)
                ->with('success', 'Visit recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to record visit. Please try again.');
        }
    }

    /**
     * Store a new photo for the case.
     */
    public function storePhoto(Request $request, OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to this case.');
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'photo_type' => 'required|string',
            'view_type' => 'required|string',
            'stage' => 'required|string',
            'photo_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('orthodontics/photos/' . $orthodonticCase->id, $fileName, 'public');

            OrthodonticPhoto::create([
                'orthodontic_case_id' => $orthodonticCase->id,
                'patient_id' => $orthodonticCase->patient_id,
                'clinic_id' => $orthodonticCase->clinic_id,
                'photo_type' => $request->photo_type,
                'view_type' => $request->view_type,
                'stage' => $request->stage,
                'photo_date' => $request->photo_date,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'notes' => $request->notes,
                'uploaded_by' => $user->id,
            ]);

            return redirect()->route('orthodontics.show', $orthodonticCase)
                ->with('success', 'Photo uploaded successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload photo. Please try again.');
        }
    }

    /**
     * Delete a photo.
     */
    public function deletePhoto(OrthodonticCase $orthodonticCase, OrthodonticPhoto $photo)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($photo->file_path)) {
                Storage::disk('public')->delete($photo->file_path);
            }

            $photo->delete();

            return back()->with('success', 'Photo deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete photo.');
        }
    }

    /**
     * Store a new payment for the case.
     */
    public function storePayment(Request $request, OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to this case.');
        }

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_type' => 'required|string',
            'installment_number' => 'nullable|integer',
            'receipt_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            OrthodonticPayment::create([
                'orthodontic_case_id' => $orthodonticCase->id,
                'patient_id' => $orthodonticCase->patient_id,
                'clinic_id' => $orthodonticCase->clinic_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'currency' => $orthodonticCase->currency,
                'payment_method' => $request->payment_method,
                'payment_type' => $request->payment_type,
                'installment_number' => $request->installment_number,
                'receipt_number' => $request->receipt_number,
                'notes' => $request->notes,
                'received_by' => $user->id,
            ]);

            return redirect()->route('orthodontics.show', $orthodonticCase)
                ->with('success', 'Payment recorded successfully!');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to record payment. Please try again.');
        }
    }

    /**
     * Get visit details for viewing.
     */
    public function getVisit(OrthodonticCase $orthodonticCase, OrthodonticVisit $visit)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access to this case.');
        }

        // Ensure the visit belongs to this case
        if ($visit->orthodontic_case_id !== $orthodonticCase->id) {
            abort(404, 'Visit not found for this case.');
        }

        return response()->json([
            'success' => true,
            'visit' => $visit,
        ]);
    }

    /**
     * Update the visual tooth chart for the case.
     */
    public function updateToothChart(Request $request, OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this case.'
            ], 403);
        }

        $request->validate([
            'tooth_states' => 'required|array',
        ]);

        try {
            $orthodonticCase->update([
                'tooth_states' => $request->tooth_states,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tooth chart updated successfully!',
                'tooth_states' => $orthodonticCase->tooth_states,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tooth chart. Please try again.',
            ], 500);
        }
    }

    /**
     * Update the current treatment phase.
     */
    public function updatePhase(Request $request, OrthodonticCase $orthodonticCase)
    {
        $user = Auth::user();

        // Check access
        if ($orthodonticCase->clinic_id !== $user->clinic_id && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this case.'
            ], 403);
        }

        $request->validate([
            'current_phase' => 'required|string|in:' . implode(',', array_keys(OrthodonticCase::TREATMENT_PHASES)),
        ]);

        try {
            $orthodonticCase->update([
                'current_phase' => $request->current_phase,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Treatment phase updated successfully!',
                'current_phase' => $orthodonticCase->current_phase,
                'phase_label' => OrthodonticCase::TREATMENT_PHASES[$orthodonticCase->current_phase] ?? $orthodonticCase->current_phase,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update treatment phase. Please try again.',
            ], 500);
        }
    }
}