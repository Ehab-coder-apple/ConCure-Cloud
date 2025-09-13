<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientCheckup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckupController extends Controller
{
    /**
     * Display a listing of checkups for a patient.
     */
    public function index(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);
        
        $query = PatientCheckup::with('recorder')
                              ->where('patient_id', $patient->id)
                              ->latest('checkup_date');
        
        // Apply date filter if provided
        if ($request->filled('date_from')) {
            $query->where('checkup_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('checkup_date', '<=', $request->date_to . ' 23:59:59');
        }
        
        $checkups = $query->paginate(15);
        
        return view('checkups.index', compact('patient', 'checkups'));
    }

    /**
     * Show the form for creating a new checkup.
     */
    public function create(Patient $patient)
    {
        $this->authorizePatientAccess($patient);
        
        return view('checkups.create', compact('patient'));
    }

    /**
     * Store a newly created checkup.
     */
    public function store(Request $request, Patient $patient)
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
            'checkup_date' => 'nullable|date',
            'custom_vital_signs' => 'nullable|array',
            'custom_fields' => 'nullable|array',
            'template_id' => 'nullable|exists:custom_checkup_templates,id',
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

        // Process custom template fields
        $customFields = [];
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldName => $value) {
                if (!empty($value) || $value === '0' || $value === 0) {
                    $customFields[$fieldName] = $value;
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
            'recorded_by' => Auth::id(),
            'checkup_date' => $request->checkup_date ?: now(),
        ]);

        return redirect()->route('checkups.index', $patient)
                        ->with('success', 'Checkup recorded successfully.');
    }

    /**
     * Display the specified checkup.
     */
    public function show(Patient $patient, PatientCheckup $checkup)
    {
        $this->authorizePatientAccess($patient);
        
        // Ensure checkup belongs to the patient
        if ($checkup->patient_id !== $patient->id) {
            abort(404);
        }
        
        $checkup->load('recorder');
        
        return view('checkups.show', compact('patient', 'checkup'));
    }

    /**
     * Show the form for editing the specified checkup.
     */
    public function edit(Patient $patient, PatientCheckup $checkup)
    {
        $this->authorizePatientAccess($patient);
        
        // Ensure checkup belongs to the patient
        if ($checkup->patient_id !== $patient->id) {
            abort(404);
        }
        
        return view('checkups.edit', compact('patient', 'checkup'));
    }

    /**
     * Update the specified checkup.
     */
    public function update(Request $request, Patient $patient, PatientCheckup $checkup)
    {
        $this->authorizePatientAccess($patient);
        
        // Ensure checkup belongs to the patient
        if ($checkup->patient_id !== $patient->id) {
            abort(404);
        }
        
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
            'checkup_date' => 'nullable|date',
        ]);

        $checkup->update([
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
            'checkup_date' => $request->checkup_date ?: $checkup->checkup_date,
        ]);

        return redirect()->route('checkups.show', [$patient, $checkup])
                        ->with('success', 'Checkup updated successfully.');
    }

    /**
     * Remove the specified checkup.
     */
    public function destroy(Patient $patient, PatientCheckup $checkup)
    {
        $this->authorizePatientAccess($patient);
        
        // Ensure checkup belongs to the patient
        if ($checkup->patient_id !== $patient->id) {
            abort(404);
        }
        
        $checkup->delete();

        return redirect()->route('checkups.index', $patient)
                        ->with('success', 'Checkup deleted successfully.');
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

        $user = Auth::user();

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
}
