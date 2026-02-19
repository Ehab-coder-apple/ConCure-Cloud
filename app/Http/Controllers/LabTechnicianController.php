<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\LabRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LabTechnicianController extends Controller
{
    /**
     * Show lab technician dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Ensure user is a lab technician
        if ($user->role !== 'lab_dept') {
            abort(403, 'Access denied. This area is for lab technicians only.');
        }

        // Get pending lab requests for this clinic
        $pendingRequests = LabRequest::with(['patient', 'doctor', 'tests'])
            ->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            })
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();

        // Get statistics
        $stats = [
            'pending_count' => $pendingRequests->count(),
            'urgent_count' => $pendingRequests->where('priority', 'urgent')->count(),
            'completed_today' => LabRequest::whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            })
            ->where('status', 'completed')
            ->whereDate('result_received_at', today())
            ->count(),
            'overdue_count' => $pendingRequests->filter(function ($request) {
                return $request->due_date && $request->due_date->isPast();
            })->count(),
        ];

        return view('lab-technician.dashboard', compact('pendingRequests', 'stats'));
    }

    /**
     * Show list of patients for direct file upload.
     * Only shows patients after search (for privacy).
     * Allows searching all clinic patients to support manual/verbal lab requests.
     */
    public function patients(Request $request)
    {
        $user = Auth::user();

        // Ensure user is a lab technician
        if ($user->role !== 'lab_dept') {
            abort(403, 'Access denied. This area is for lab technicians only.');
        }

        $patients = collect();
        $searchTerm = $request->input('search', '');

        // Only show patients if search term is provided
        if ($request->filled('search')) {
            $search = trim($searchTerm);

            // Query all patients in the clinic that match search criteria
            $patients = Patient::where('clinic_id', $user->clinic_id)
                ->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('patient_id', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                })
                ->orderBy('first_name', 'asc')
                ->orderBy('last_name', 'asc')
                ->paginate(20)
                ->appends(['search' => $searchTerm]);
        }

        return view('lab-technician.patients', compact('patients', 'searchTerm'));
    }

    /**
     * Show patient files and upload form.
     */
    public function showPatientFiles(Patient $patient)
    {
        $user = Auth::user();

        // Ensure user is a lab technician
        if ($user->role !== 'lab_dept') {
            abort(403, 'Access denied. This area is for lab technicians only.');
        }

        // Ensure patient belongs to the same clinic
        if ($patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to patient files.');
        }

        // Get lab result files for this patient
        $labResults = PatientFile::where('patient_id', $patient->id)
            ->where('category', 'lab_result')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('lab-technician.patient-files', compact('patient', 'labResults'));
    }

    /**
     * Upload lab result file directly to patient's files.
     */
    public function uploadPatientFile(Request $request, Patient $patient)
    {
        $user = Auth::user();

        // Ensure user is a lab technician
        if ($user->role !== 'lab_dept') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This area is for lab technicians only.'
            ], 403);
        }

        // Ensure patient belongs to the same clinic
        if ($patient->clinic_id !== $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to patient files.'
            ], 403);
        }

        $request->validate([
            'result_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'description' => 'nullable|string|max:500',
            'test_name' => 'nullable|string|max:255',
        ]);

        try {
            $file = $request->file('result_file');

            // Generate unique filename
            $filename = 'lab_result_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs("patients/{$patient->id}/lab_results", $filename, 'public');

            // Create patient file record
            $patientFile = PatientFile::create([
                'patient_id' => $patient->id,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'category' => 'lab_result',
                'description' => $request->description ?: ($request->test_name ? "Lab result: {$request->test_name}" : "Lab result uploaded by lab technician"),
                'uploaded_by' => $user->id,
            ]);

            Log::info('Lab result uploaded directly by lab technician', [
                'patient_id' => $patient->id,
                'file_id' => $patientFile->id,
                'uploaded_by' => $user->id,
                'test_name' => $request->test_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lab result uploaded successfully',
                'file_url' => Storage::url($path),
                'file' => $patientFile,
            ]);

        } catch (\Exception $e) {
            Log::error('Lab result upload failed', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload lab result: ' . $e->getMessage(),
            ], 500);
        }
    }
}

