<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientVitalSignsAssignment;
use App\Models\CustomVitalSignsConfig;
use App\Models\MedicalConditionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientVitalSignsController extends Controller
{
    /**
     * Display patient's vital signs assignments.
     */
    public function index(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $assignments = $patient->vitalSignsAssignments()
                              ->with(['customVitalSign', 'assignedBy'])
                              ->orderBy('is_active', 'desc')
                              ->orderBy('assigned_at', 'desc')
                              ->get();

        $availableVitalSigns = CustomVitalSignsConfig::forClinic($patient->clinic_id)
                                                    ->active()
                                                    ->whereNotIn('id', $assignments->where('is_active', true)->pluck('custom_vital_sign_id'))
                                                    ->ordered()
                                                    ->get();

        $medicalTemplates = MedicalConditionTemplate::forClinic($patient->clinic_id)
                                                   ->active()
                                                   ->get();

        return view('patients.vital-signs.index', compact('patient', 'assignments', 'availableVitalSigns', 'medicalTemplates'));
    }

    /**
     * Assign vital sign to patient.
     */
    public function assign(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'custom_vital_sign_id' => 'required|exists:custom_vital_signs_config,id',
            'medical_condition' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:500',
        ]);

        $vitalSign = CustomVitalSignsConfig::findOrFail($request->custom_vital_sign_id);

        // Ensure vital sign belongs to the same clinic
        if ($vitalSign->clinic_id !== $patient->clinic_id) {
            abort(403, 'Unauthorized access to vital sign.');
        }

        $assignment = PatientVitalSignsAssignment::assignVitalSign(
            $patient,
            $vitalSign,
            Auth::user(),
            $request->medical_condition,
            $request->reason
        );

        return redirect()->route('patients.vital-signs.index', $patient)
                        ->with('success', "Vital sign '{$vitalSign->name}' assigned successfully.");
    }

    /**
     * Assign vital signs from medical condition template.
     */
    public function assignFromTemplate(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'template_id' => 'required|exists:medical_condition_templates,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $template = MedicalConditionTemplate::findOrFail($request->template_id);

        // Ensure template belongs to the same clinic
        if ($template->clinic_id !== $patient->clinic_id) {
            abort(403, 'Unauthorized access to template.');
        }

        $assignments = PatientVitalSignsAssignment::assignFromTemplate(
            $patient,
            $template,
            Auth::user(),
            $request->reason
        );

        $count = count($assignments);
        $message = $count > 0 
            ? "Successfully assigned {$count} vital sign(s) from '{$template->condition_name}' template."
            : "All vital signs from '{$template->condition_name}' template were already assigned.";

        return redirect()->route('patients.vital-signs.index', $patient)
                        ->with('success', $message);
    }

    /**
     * Update vital sign assignment.
     */
    public function update(Request $request, Patient $patient, PatientVitalSignsAssignment $assignment)
    {
        $this->authorizePatientAccess($patient);

        // Ensure assignment belongs to this patient
        if ($assignment->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to assignment.');
        }

        $request->validate([
            'medical_condition' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $assignment->update([
            'medical_condition' => $request->medical_condition,
            'reason' => $request->reason,
            'is_active' => $request->boolean('is_active', true),
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
        ]);

        return redirect()->route('patients.vital-signs.index', $patient)
                        ->with('success', 'Vital sign assignment updated successfully.');
    }

    /**
     * Remove vital sign assignment.
     */
    public function destroy(Patient $patient, PatientVitalSignsAssignment $assignment)
    {
        $this->authorizePatientAccess($patient);

        // Ensure assignment belongs to this patient
        if ($assignment->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to assignment.');
        }

        $vitalSignName = $assignment->customVitalSign->name;
        $assignment->deactivate(Auth::user(), 'Manually removed');

        return redirect()->route('patients.vital-signs.index', $patient)
                        ->with('success', "Vital sign '{$vitalSignName}' removed successfully.");
    }

    /**
     * Toggle vital sign assignment status.
     */
    public function toggle(Patient $patient, PatientVitalSignsAssignment $assignment)
    {
        $this->authorizePatientAccess($patient);

        // Ensure assignment belongs to this patient
        if ($assignment->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to assignment.');
        }

        $newStatus = !$assignment->is_active;
        $assignment->update([
            'is_active' => $newStatus,
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
        ]);

        $status = $newStatus ? 'activated' : 'deactivated';
        $vitalSignName = $assignment->customVitalSign->name;

        return redirect()->route('patients.vital-signs.index', $patient)
                        ->with('success', "Vital sign '{$vitalSignName}' {$status} successfully.");
    }

    /**
     * Get available vital signs for AJAX requests.
     */
    public function getAvailableVitalSigns(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $assignedIds = $patient->activeVitalSignsAssignments()->pluck('custom_vital_sign_id');
        
        $availableVitalSigns = CustomVitalSignsConfig::forClinic($patient->clinic_id)
                                                    ->active()
                                                    ->whereNotIn('id', $assignedIds)
                                                    ->ordered()
                                                    ->get(['id', 'name', 'unit', 'type', 'normal_range']);

        return response()->json($availableVitalSigns);
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
            !in_array($user->role, ['doctor', 'admin', 'nurse'])) {
            abort(403, 'Insufficient permissions to view patients.');
        }
    }
}
