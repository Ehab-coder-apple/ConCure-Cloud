<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\RadiologyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RadiologyTechnicianController extends Controller
{
    /**
     * Show radiology technician dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Ensure user is a radiology technician
        if ($user->role !== 'radiology_dept') {
            abort(403, 'Access denied. This area is for radiology technicians only.');
        }

        // Get pending radiology requests for this clinic
        $pendingRequests = RadiologyRequest::with(['patient', 'doctor', 'tests'])
            ->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            })
            ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();

        // Get completed requests (last 30 days)
        $completedRequests = RadiologyRequest::with(['patient', 'doctor'])
            ->whereHas('patient', function ($q) use ($user) {
                $q->where('clinic_id', $user->clinic_id);
            })
            ->where('status', 'completed')
            ->where('result_received_at', '>=', now()->subDays(30))
            ->orderBy('result_received_at', 'desc')
            ->get();

        // Statistics
        $stats = [
            'pending' => $pendingRequests->where('status', 'pending')->count(),
            'scheduled' => $pendingRequests->where('status', 'scheduled')->count(),
            'in_progress' => $pendingRequests->where('status', 'in_progress')->count(),
            'completed_today' => $completedRequests->where('result_received_at', '>=', now()->startOfDay())->count(),
        ];

        return view('radiology-technician.dashboard', compact('pendingRequests', 'completedRequests', 'stats'));
    }

    /**
     * Show list of patients for direct file upload.
     * Only shows patients after search (for privacy).
     * Allows searching all clinic patients to support manual/verbal radiology requests.
     */
    public function patients(Request $request)
    {
        $user = Auth::user();

        // Ensure user is a radiology technician
        if ($user->role !== 'radiology_dept') {
            abort(403, 'Access denied. This area is for radiology technicians only.');
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

        return view('radiology-technician.patients', compact('patients', 'searchTerm'));
    }

    /**
     * Show patient files upload page.
     */
    public function showPatientFiles(Patient $patient)
    {
        $user = Auth::user();

        // Ensure user is a radiology technician
        if ($user->role !== 'radiology_dept') {
            abort(403, 'Access denied. This area is for radiology technicians only.');
        }

        // Ensure patient belongs to the same clinic
        if ($patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Access denied. This patient does not belong to your clinic.');
        }

        // Get patient's radiology-related files
        $files = PatientFile::where('patient_id', $patient->id)
            ->where('file_type', 'radiology_result')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('radiology-technician.patient-files', compact('patient', 'files'));
    }

    /**
     * Upload a file for a patient.
     */
    public function uploadPatientFile(Request $request, Patient $patient)
    {
        $user = Auth::user();

        // Ensure user is a radiology technician
        if ($user->role !== 'radiology_dept') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This area is for radiology technicians only.'
            ], 403);
        }

        // Ensure patient belongs to the same clinic
        if ($patient->clinic_id !== $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This patient does not belong to your clinic.'
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,dicom|max:20480',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            // Handle file upload
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Store in private disk
            $filePath = $file->storeAs('patient_files/' . $patient->id, $fileName, 'private');

            // Create file record
            PatientFile::create([
                'patient_id' => $patient->id,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => 'radiology_result',
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'description' => $request->description,
                'uploaded_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Radiology result uploaded successfully.'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Radiology file upload failed', [
                'patient_id' => $patient->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to upload file. Please try again or contact support.'),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

