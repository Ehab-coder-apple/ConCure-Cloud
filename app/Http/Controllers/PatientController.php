<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCheckup;
use App\Models\PatientFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Patient::with(['clinic', 'creator', 'checkups' => function ($q) {
            $q->latest('checkup_date')->limit(1);
        }]);

        // All clinic users are restricted to their clinic
        $query->byClinic($user->clinic_id);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('age_range')) {
            $ageRange = explode('-', $request->age_range);
            if (count($ageRange) === 2) {
                $query->byAgeRange((int) $ageRange[0], (int) $ageRange[1]);
            }
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'whatsapp_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'job' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:50|max:300',
            'weight' => 'nullable|numeric|min:1|max:500',
            'allergies' => 'nullable|string',
            'is_pregnant' => 'boolean',
            'chronic_illnesses' => 'nullable|string',
            'surgeries_history' => 'nullable|string',
            'diet_history' => 'nullable|string',
            'notes' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        $patient = Patient::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'job' => $request->job,
            'education' => $request->education,
            'height' => $request->height,
            'weight' => $request->weight,
            'allergies' => $request->allergies,
            'is_pregnant' => $request->boolean('is_pregnant'),
            'chronic_illnesses' => $request->chronic_illnesses,
            'surgeries_history' => $request->surgeries_history,
            'diet_history' => $request->diet_history,
            'notes' => $request->notes,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'is_active' => true,
        ]);

        return redirect()->route('patients.show', $patient)
                        ->with('success', 'Patient created successfully.');
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient)
    {
        $this->authorizePatientAccess($patient);
        
        $patient->load([
            'clinic',
            'creator',
            'checkups' => function ($q) {
                $q->with('recorder')->latest('checkup_date')->limit(10);
            },
            'files' => function ($q) {
                $q->with('uploader')->latest()->limit(10);
            },
            'prescriptions' => function ($q) {
                $q->with('doctor')->latest()->limit(5);
            },
            'simplePrescriptions' => function ($q) {
                $q->with('doctor')->latest()->limit(5);
            },
            'appointments' => function ($q) {
                $q->with('doctor')->latest('appointment_datetime')->limit(5);
            }
        ]);

        return view('patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient)
    {
        $this->authorizePatientAccess($patient);
        
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'whatsapp_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'job' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:50|max:300',
            'weight' => 'nullable|numeric|min:1|max:500',
            'allergies' => 'nullable|string',
            'is_pregnant' => 'boolean',
            'chronic_illnesses' => 'nullable|string',
            'surgeries_history' => 'nullable|string',
            'diet_history' => 'nullable|string',
            'notes' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $patient->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'job' => $request->job,
            'education' => $request->education,
            'height' => $request->height,
            'weight' => $request->weight,
            'allergies' => $request->allergies,
            'is_pregnant' => $request->boolean('is_pregnant'),
            'chronic_illnesses' => $request->chronic_illnesses,
            'surgeries_history' => $request->surgeries_history,
            'diet_history' => $request->diet_history,
            'notes' => $request->notes,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('patients.show', $patient)
                        ->with('success', 'Patient updated successfully.');
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(Patient $patient)
    {
        $this->authorizePatientAccess($patient);
        
        // Check if patient has any related records
        if ($patient->prescriptions()->count() > 0 || 
            $patient->appointments()->count() > 0 || 
            $patient->invoices()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete patient with existing medical records. Deactivate instead.']);
        }

        $patient->delete();

        return redirect()->route('patients.index')
                        ->with('success', 'Patient deleted successfully.');
    }

    /**
     * Show patient history.
     */
    public function history(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $patient->load([
            'checkups' => function ($q) {
                $q->with('recorder')->latest('checkup_date');
            },
            'prescriptions' => function ($q) {
                $q->with(['doctor', 'medicines'])->latest('prescribed_date');
            },
            'labRequests' => function ($q) {
                $q->with(['doctor', 'tests'])->latest('requested_date');
            },
            'dietPlans' => function ($q) {
                $q->with('doctor')->latest('start_date');
            },
            'appointments' => function ($q) {
                $q->with('doctor')->latest('appointment_datetime');
            }
        ]);

        return view('patients.history', compact('patient'));
    }

    /**
     * Get patients list for API/AJAX requests.
     */
    public function apiList(Request $request)
    {
        $user = auth()->user();

        $query = Patient::where('clinic_id', $user->clinic_id)
                        ->select('id', 'patient_id', 'first_name', 'last_name')
                        ->orderBy('first_name')
                        ->orderBy('last_name');

        // Add search functionality if needed
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%");
            });
        }

        $patients = $query->get();

        return response()->json([
            'success' => true,
            'data' => $patients,
            'count' => $patients->count()
        ]);
    }

    /**
     * Add a new checkup for the patient.
     */
    public function addCheckup(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);
        
        $request->validate([
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:50|max:300',
            'blood_pressure' => 'nullable|string|regex:/^\d{2,3}\/\d{2,3}$/',
            'heart_rate' => 'nullable|integer|min:30|max:200',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'respiratory_rate' => 'nullable|integer|min:5|max:50',
            'blood_sugar' => 'nullable|numeric|min:20|max:600',
            'symptoms' => 'nullable|string',
            'notes' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        PatientCheckup::create([
            'patient_id' => $patient->id,
            'weight' => $request->weight,
            'height' => $request->height,
            'blood_pressure' => $request->blood_pressure,
            'heart_rate' => $request->heart_rate,
            'temperature' => $request->temperature,
            'respiratory_rate' => $request->respiratory_rate,
            'blood_sugar' => $request->blood_sugar,
            'symptoms' => $request->symptoms,
            'notes' => $request->notes,
            'recommendations' => $request->recommendations,
            'recorded_by' => auth()->id(),
            'checkup_date' => now(),
        ]);

        return back()->with('success', 'Checkup recorded successfully.');
    }

    /**
     * Upload a file for the patient.
     */
    public function uploadFile(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);
        
        $request->validate([
            'file' => 'required|file|max:' . config('app.concure.max_file_size'),
            'category' => 'required|in:lab_result,medicine_photo,medical_report,other',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $allowedTypes = config('app.concure.allowed_file_types');
        
        if (!in_array(strtolower($file->getClientOriginalExtension()), $allowedTypes)) {
            return back()->withErrors(['file' => 'File type not allowed.']);
        }

        // Generate unique filename
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs("patients/{$patient->id}/files", $filename, 'public');

        PatientFile::create([
            'patient_id' => $patient->id,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $filename,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'category' => $request->category,
            'description' => $request->description,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    /**
     * Authorize access to patient.
     */
    private function authorizePatientAccess(Patient $patient): void
    {
        // DEVELOPMENT MODE: Completely disable patient access authorization
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return; // Allow all access during development
        }

        $user = auth()->user();

        // Users can only access patients in their clinic
        if ($patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to patient.');
        }

        // Check permission-based access or role-based fallback
        if (!$user->hasPermission('patients_view') &&
            !$user->canManagePatients() &&
            !in_array($user->role, ['doctor', 'admin', 'nurse'])) {
            abort(403, 'Insufficient permissions to view patients.');
        }
    }
}
