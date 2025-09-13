<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\RadiologyRequest;
use App\Models\RadiologyRequestTest;
use App\Models\RadiologyTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RadiologyController extends Controller
{
    /**
     * Display a listing of radiology requests.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user can view radiology requests
        if (!$user->canViewRadiologyRequests()) {
            abort(403, 'You do not have permission to view radiology requests.');
        }

        $query = RadiologyRequest::with(['patient', 'doctor', 'tests']);

        // Filter by clinic
        $query->byClinic($user->clinic_id);

        // Filter by doctor if user is a doctor
        if (in_array($user->role, ['doctor', 'nutritionist'])) {
            $query->byDoctor($user->id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('patient_id')) {
            $query->byPatient($request->patient_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $radiologyRequests = $query->latest()->paginate(15);

        // Get statistics
        $stats = [
            'total' => RadiologyRequest::byClinic($user->clinic_id)->count(),
            'pending' => RadiologyRequest::byClinic($user->clinic_id)->byStatus('pending')->count(),
            'completed' => RadiologyRequest::byClinic($user->clinic_id)->byStatus('completed')->count(),
            'urgent' => RadiologyRequest::byClinic($user->clinic_id)->byPriority('urgent')->count(),
        ];

        // Get patients for filter dropdown
        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        return view('radiology.index', compact('radiologyRequests', 'stats', 'patients'));
    }

    /**
     * Show the form for creating a new radiology request.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!$user->canCreateRadiologyRequests()) {
            abort(403, 'You do not have permission to create radiology requests.');
        }

        // Get patients for dropdown
        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        // Get radiology tests
        $radiologyTests = RadiologyTest::where(function ($query) use ($user) {
            $query->whereNull('clinic_id')
                  ->orWhere('clinic_id', $user->clinic_id);
        })->active()->ordered()->get();

        // Pre-select patient if provided
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::where('id', $request->patient_id)
                                    ->where('clinic_id', $user->clinic_id)
                                    ->first();
        }

        return view('radiology.create', compact('patients', 'radiologyTests', 'selectedPatient'));
    }

    /**
     * Store a newly created radiology request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->canCreateRadiologyRequests()) {
            abort(403, 'You do not have permission to create radiology requests.');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'clinical_notes' => 'nullable|string',
            'clinical_history' => 'nullable|string',
            'suspected_diagnosis' => 'nullable|string',
            'due_date' => 'nullable|date|after:today',
            'priority' => 'required|in:normal,urgent,stat',
            'radiology_center_name' => 'nullable|string|max:255',
            'radiology_center_phone' => 'nullable|string|max:20',
            'radiology_center_whatsapp' => 'nullable|string|max:20',
            'radiology_center_email' => 'nullable|email|max:255',
            'radiology_center_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'tests' => 'required|array|min:1',
            'tests.*.test_name' => 'required|string',
            'tests.*.radiology_test_id' => 'nullable|exists:radiology_tests,id',
            'tests.*.instructions' => 'nullable|string',
            'tests.*.clinical_indication' => 'nullable|string',
            'tests.*.with_contrast' => 'nullable|boolean',
            'tests.*.urgent' => 'nullable|boolean',
            'tests.*.special_requirements' => 'nullable|string',
        ]);

        // Verify patient belongs to user's clinic
        $patient = Patient::where('id', $request->patient_id)
                         ->where('clinic_id', $user->clinic_id)
                         ->firstOrFail();

        try {
            DB::beginTransaction();

            $radiologyRequest = RadiologyRequest::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $user->id,
                'clinical_notes' => $request->clinical_notes,
                'clinical_history' => $request->clinical_history,
                'suspected_diagnosis' => $request->suspected_diagnosis,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'radiology_center_name' => $request->radiology_center_name,
                'radiology_center_phone' => $request->radiology_center_phone,
                'radiology_center_whatsapp' => $request->radiology_center_whatsapp,
                'radiology_center_email' => $request->radiology_center_email,
                'radiology_center_address' => $request->radiology_center_address,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            foreach ($request->tests as $testData) {
                $radiologyRequest->addTest([
                    'radiology_test_id' => $testData['radiology_test_id'] ?? null,
                    'test_name' => $testData['test_name'],
                    'instructions' => $testData['instructions'] ?? null,
                    'clinical_indication' => $testData['clinical_indication'] ?? null,
                    'with_contrast' => $testData['with_contrast'] ?? false,
                    'urgent' => $testData['urgent'] ?? false,
                    'special_requirements' => $testData['special_requirements'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('recommendations.radiology.show', $radiologyRequest)
                           ->with('success', 'Radiology request created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                        ->with('error', 'Failed to create radiology request. Please try again.');
        }
    }

    /**
     * Display the specified radiology request.
     */
    public function show(RadiologyRequest $radiologyRequest)
    {
        $user = Auth::user();

        // Check access
        if ($radiologyRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to radiology request.');
        }

        $radiologyRequest->load(['patient', 'doctor', 'tests.radiologyTest', 'resultReceiver']);

        return view('radiology.show', compact('radiologyRequest'));
    }

    /**
     * Show the form for editing the specified radiology request.
     */
    public function edit(RadiologyRequest $radiologyRequest)
    {
        $user = Auth::user();

        // Check access and permissions
        if ($radiologyRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to radiology request.');
        }

        if (!$user->canEditRadiologyRequests()) {
            abort(403, 'You do not have permission to edit radiology requests.');
        }

        // Only allow editing of pending requests
        if ($radiologyRequest->status !== 'pending') {
            return back()->with('error', 'Only pending radiology requests can be edited.');
        }

        // Get patients for dropdown
        $patients = Patient::where('clinic_id', $user->clinic_id)
                          ->where('is_active', true)
                          ->orderBy('first_name')
                          ->get();

        // Get radiology tests
        $radiologyTests = RadiologyTest::where(function ($query) use ($user) {
            $query->whereNull('clinic_id')
                  ->orWhere('clinic_id', $user->clinic_id);
        })->active()->ordered()->get();

        $radiologyRequest->load(['tests.radiologyTest']);

        return view('radiology.edit', compact('radiologyRequest', 'patients', 'radiologyTests'));
    }

    /**
     * Update the specified radiology request.
     */
    public function update(Request $request, RadiologyRequest $radiologyRequest)
    {
        $user = Auth::user();

        // Check access and permissions
        if ($radiologyRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to radiology request.');
        }

        if (!$user->canEditRadiologyRequests()) {
            abort(403, 'You do not have permission to update radiology requests.');
        }

        // Only allow editing of pending requests
        if ($radiologyRequest->status !== 'pending') {
            return back()->with('error', 'Only pending radiology requests can be edited.');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'clinical_notes' => 'nullable|string',
            'clinical_history' => 'nullable|string',
            'suspected_diagnosis' => 'nullable|string',
            'due_date' => 'nullable|date|after:today',
            'priority' => 'required|in:normal,urgent,stat',
            'radiology_center_name' => 'nullable|string|max:255',
            'radiology_center_phone' => 'nullable|string|max:20',
            'radiology_center_whatsapp' => 'nullable|string|max:20',
            'radiology_center_email' => 'nullable|email|max:255',
            'radiology_center_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'tests' => 'required|array|min:1',
            'tests.*.test_name' => 'required|string',
            'tests.*.radiology_test_id' => 'nullable|exists:radiology_tests,id',
            'tests.*.instructions' => 'nullable|string',
            'tests.*.clinical_indication' => 'nullable|string',
            'tests.*.with_contrast' => 'nullable|boolean',
            'tests.*.urgent' => 'nullable|boolean',
            'tests.*.special_requirements' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $radiologyRequest->update($request->only([
                'patient_id', 'clinical_notes', 'clinical_history', 'suspected_diagnosis',
                'due_date', 'priority', 'radiology_center_name', 'radiology_center_phone',
                'radiology_center_whatsapp', 'radiology_center_email', 'radiology_center_address', 'notes'
            ]));

            // Delete existing tests and recreate them
            $radiologyRequest->tests()->delete();

            foreach ($request->tests as $testData) {
                $radiologyRequest->addTest([
                    'radiology_test_id' => $testData['radiology_test_id'] ?? null,
                    'test_name' => $testData['test_name'],
                    'instructions' => $testData['instructions'] ?? null,
                    'clinical_indication' => $testData['clinical_indication'] ?? null,
                    'with_contrast' => $testData['with_contrast'] ?? false,
                    'urgent' => $testData['urgent'] ?? false,
                    'special_requirements' => $testData['special_requirements'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('recommendations.radiology.show', $radiologyRequest)
                           ->with('success', 'Radiology request updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                        ->with('error', 'Failed to update radiology request. Please try again.');
        }
    }

    /**
     * Remove the specified radiology request.
     */
    public function destroy(RadiologyRequest $radiologyRequest)
    {
        $user = Auth::user();

        // Check access and permissions
        if ($radiologyRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to radiology request.');
        }

        if (!$user->canDeleteRadiologyRequests()) {
            abort(403, 'You do not have permission to delete radiology requests.');
        }

        // Only allow deletion of pending requests
        if ($radiologyRequest->status !== 'pending') {
            return back()->with('error', 'Only pending radiology requests can be deleted.');
        }

        $radiologyRequest->delete();

        return redirect()->route('recommendations.radiology.index')
                       ->with('success', 'Radiology request deleted successfully.');
    }

    /**
     * Generate PDF for radiology request.
     */
    public function pdf(RadiologyRequest $radiologyRequest)
    {
        $user = Auth::user();

        // Check access
        if ($radiologyRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to radiology request.');
        }

        $radiologyRequest->load(['patient', 'doctor', 'tests.radiologyTest']);

        $pdf = Pdf::loadView('radiology.pdf', compact('radiologyRequest'));

        return $pdf->download('radiology-request-' . $radiologyRequest->request_number . '.pdf');
    }

    /**
     * Update radiology request status.
     */
    public function updateStatus(Request $request, RadiologyRequest $radiologyRequest)
    {
        $user = Auth::user();

        // Check access
        if ($radiologyRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to radiology request.');
        }

        if (!$user->canEditRadiologyRequests()) {
            abort(403, 'You do not have permission to update radiology request status.');
        }

        $request->validate([
            'status' => 'required|in:pending,scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $radiologyRequest->update([
            'status' => $request->status,
            'notes' => $request->notes ? $radiologyRequest->notes . "\n\n" . now()->format('Y-m-d H:i') . ": " . $request->notes : $radiologyRequest->notes,
        ]);

        return back()->with('success', 'Radiology request status updated successfully.');
    }

    /**
     * Upload radiology result file.
     */
    public function uploadResult(Request $request, RadiologyRequest $radiologyRequest)
    {
        $user = Auth::user();

        // Check access
        if ($radiologyRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to radiology request.');
        }

        if (!$user->canEditRadiologyRequests()) {
            abort(403, 'You do not have permission to upload radiology results.');
        }

        $request->validate([
            'result_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'radiologist_report' => 'nullable|string',
            'findings' => 'nullable|string',
            'impression' => 'nullable|string',
        ]);

        try {
            // Store the uploaded file
            $file = $request->file('result_file');
            $filename = 'radiology-result-' . $radiologyRequest->request_number . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('radiology-results', $filename, 'public');

            // Update radiology request with result information
            $radiologyRequest->update([
                'result_file_path' => $path,
                'radiologist_report' => $request->radiologist_report,
                'findings' => $request->findings,
                'impression' => $request->impression,
                'result_received_at' => now(),
                'result_received_by' => $user->id,
                'status' => 'completed',
            ]);

            return back()->with('success', 'Radiology result uploaded successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload radiology result. Please try again.');
        }
    }

    /**
     * Get radiology tests by category (AJAX).
     */
    public function getTestsByCategory(Request $request)
    {
        $user = Auth::user();
        $category = $request->get('category');

        $tests = RadiologyTest::where(function ($query) use ($user) {
            $query->whereNull('clinic_id')
                  ->orWhere('clinic_id', $user->clinic_id);
        })
        ->active()
        ->when($category, function ($query, $category) {
            return $query->byCategory($category);
        })
        ->ordered()
        ->get();

        return response()->json($tests);
    }

    /**
     * Search radiology tests (AJAX).
     */
    public function searchTests(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search');
        $category = $request->get('category');

        $tests = RadiologyTest::where(function ($query) use ($user) {
            $query->whereNull('clinic_id')
                  ->orWhere('clinic_id', $user->clinic_id);
        })
        ->active()
        ->when($category, function ($query, $category) {
            return $query->where('category', $category);
        })
        ->when($search, function ($query, $search) {
            return $query->search($search);
        })
        ->ordered()
        ->limit(50)
        ->get();

        return response()->json($tests);
    }

    /**
     * Create a custom radiology test (AJAX).
     */
    public function createCustomTest(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'category' => 'required|string|max:100',
            'body_part' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'preparation_instructions' => 'nullable|string',
            'estimated_duration_minutes' => 'nullable|integer|min:1|max:480',
            'requires_contrast' => 'boolean',
            'requires_fasting' => 'boolean',
        ]);

        try {
            $test = RadiologyTest::create([
                'name' => $request->name,
                'code' => $request->code,
                'category' => $request->category,
                'body_part' => $request->body_part,
                'description' => $request->description,
                'preparation_instructions' => $request->preparation_instructions,
                'estimated_duration_minutes' => $request->estimated_duration_minutes,
                'requires_contrast' => $request->boolean('requires_contrast'),
                'requires_fasting' => $request->boolean('requires_fasting'),
                'clinic_id' => $user->clinic_id,
                'is_frequent' => false,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Custom test created successfully',
                'test' => $test
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create custom test'
            ], 500);
        }
    }

    /**
     * Show test management page.
     */
    public function manageTests()
    {
        $user = Auth::user();

        if (!$user->canEditRadiologyRequests()) {
            abort(403, 'You do not have permission to manage radiology tests.');
        }

        $tests = RadiologyTest::where(function ($query) use ($user) {
            $query->whereNull('clinic_id')
                  ->orWhere('clinic_id', $user->clinic_id);
        })
        ->active()
        ->ordered()
        ->paginate(20);

        $categories = RadiologyTest::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        return view('radiology.manage-tests', compact('tests', 'categories'));
    }

    /**
     * Delete a custom radiology test.
     */
    public function deleteTest(RadiologyTest $radiologyTest)
    {
        $user = Auth::user();

        if (!$user->canEditRadiologyRequests()) {
            abort(403, 'You do not have permission to delete radiology tests.');
        }

        // Only allow deletion of clinic-specific tests
        if ($radiologyTest->clinic_id !== $user->clinic_id) {
            abort(403, 'You can only delete tests created by your clinic.');
        }

        try {
            $radiologyTest->delete();
            return back()->with('success', 'Test deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete test.');
        }
    }
}
