<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCheckup;
use App\Models\PatientFile;
use App\Imports\PatientsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Check if user has a clinic assigned
        if (!$user->clinic_id) {
            return redirect()->route('dashboard')
                           ->with('error', 'You must be assigned to a clinic to view patients. Please contact your administrator.');
        }

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

        // Check if user has a clinic assigned
        if (!$user->clinic_id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be assigned to a clinic to create patients. Please contact your administrator.'
                ], 403);
            }

            return redirect()->route('patients.index')
                           ->with('error', 'You must be assigned to a clinic to create patients. Please contact your administrator.');
        }

        // Adjust validation rules for quick add (AJAX requests)
        $isQuickAdd = $request->wantsJson() || $request->ajax();

        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'date_of_birth' => $isQuickAdd ? 'nullable|date|before:today' : 'required|date|before:today',
            'gender' => $isQuickAdd ? 'nullable|in:male,female,other' : 'required|in:male,female,other',
            'whatsapp_phone' => 'nullable|string|max:20',
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
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($isQuickAdd) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

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

        // Return JSON response for AJAX requests
        if ($isQuickAdd) {
            return response()->json([
                'success' => true,
                'message' => 'Patient created successfully',
                'patient' => [
                    'id' => $patient->id,
                    'patient_id' => $patient->patient_id,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'full_name' => $patient->first_name . ' ' . $patient->last_name
                ]
            ]);
        }

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

        // Check if user has a clinic assigned
        if (!$user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'You must be assigned to a clinic to access patients.',
                'data' => [],
                'count' => 0
            ], 403);
        }

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
        
        $validationRules = [
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
            'custom_vital_signs' => 'nullable|array',
        ];

        // Add validation rules for custom vital signs
        $customSigns = \App\Models\CustomVitalSignsConfig::forClinic($patient->clinic_id)
                                                         ->active()
                                                         ->get();

        foreach ($customSigns as $sign) {
            $fieldName = "custom_vital_signs.{$sign->id}";
            $rules = ['nullable'];

            if ($sign->type === 'number') {
                $rules[] = 'numeric';
                if ($sign->min_value) $rules[] = "min:{$sign->min_value}";
                if ($sign->max_value) $rules[] = "max:{$sign->max_value}";
            } elseif ($sign->type === 'select' && $sign->options) {
                $rules[] = 'in:' . implode(',', array_keys($sign->options));
            }

            $validationRules[$fieldName] = implode('|', $rules);
        }

        $request->validate($validationRules);

        // Process custom vital signs
        $customVitalSigns = [];
        if ($request->has('custom_vital_signs')) {
            foreach ($request->custom_vital_signs as $configId => $value) {
                if ($value !== null && $value !== '') {
                    $customVitalSigns[$configId] = $value;
                }
            }
        }

        PatientCheckup::create([
            'patient_id' => $patient->id,
            'weight' => $request->weight,
            'height' => $request->height,
            'blood_pressure' => $request->blood_pressure,
            'heart_rate' => $request->heart_rate,
            'temperature' => $request->temperature,
            'respiratory_rate' => $request->respiratory_rate,
            'blood_sugar' => $request->blood_sugar,
            'custom_vital_signs' => $customVitalSigns ?: null,
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
     * Check patient permission (disabled in development mode)
     */
    private function checkPatientPermission($permission)
    {
        // DEVELOPMENT MODE: Disable all authorization checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return;
        }

        $user = auth()->user();
        if (!$user || !$user->hasPermission($permission)) {
            abort(403, 'Unauthorized access to patient management.');
        }
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
            !in_array($user->role, ['doctor', 'admin', 'nutritionist', 'nurse'])) {
            abort(403, 'Insufficient permissions to view patients.');
        }
    }

    /**
     * Show the import form.
     */
    public function showImport()
    {
        $this->checkPatientPermission('patients_create');

        return view('patients.import');
    }

    /**
     * Download the import template.
     */
    public function downloadTemplate(Request $request)
    {
        $this->checkPatientPermission('patients_create');

        $includeSampleData = $request->boolean('sample', true);
        $format = $request->get('format', 'xlsx'); // Default to Excel

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Get headers
        $headers = PatientsImport::getExpectedHeaders();
        $headerKeys = array_keys($headers);
        $headerValues = array_values($headers);

        // Set headers
        $sheet->fromArray([$headerKeys], null, 'A1');
        $sheet->fromArray([$headerValues], null, 'A2');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:' . chr(64 + count($headerKeys)) . '2')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', chr(64 + count($headerKeys))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add sample data if requested
        if ($includeSampleData) {
            $sampleData = PatientsImport::getSampleData();
            $startRow = 3;
            foreach ($sampleData as $rowData) {
                $rowValues = [];
                foreach ($headerKeys as $key) {
                    $rowValues[] = $rowData[$key] ?? '';
                }
                $sheet->fromArray([$rowValues], null, 'A' . $startRow);
                $startRow++;
            }
        }

        // Generate filename
        $filename = 'patients_import_template_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Create writer and return response
        $writer = new Xlsx($spreadsheet);

        return response()->stream(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import patients from uploaded file.
     */
    public function import(Request $request)
    {
        $this->checkPatientPermission('patients_create');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new PatientsImport();

            Excel::import($import, $request->file('file'));

            $message = "Import completed successfully! ";
            $message .= "Imported: {$import->getImportedCount()} patients. ";

            if ($import->getSkippedCount() > 0) {
                $message .= "Skipped: {$import->getSkippedCount()} patients (duplicates or errors).";
            }

            if ($import->hasErrors()) {
                $errorMessage = "Some patients could not be imported:\n" . implode("\n", array_slice($import->getErrors(), 0, 10));
                if (count($import->getErrors()) > 10) {
                    $errorMessage .= "\n... and " . (count($import->getErrors()) - 10) . " more errors.";
                }

                return redirect()->route('patients.import')
                    ->with('warning', $message)
                    ->with('import_errors', $errorMessage);
            }

            return redirect()->route('patients.import')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('patients.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
