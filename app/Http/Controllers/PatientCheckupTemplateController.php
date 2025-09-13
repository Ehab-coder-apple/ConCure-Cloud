<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\CustomCheckupTemplate;
use App\Models\PatientCheckupTemplateAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientCheckupTemplateController extends Controller
{
    /**
     * Display patient's checkup template assignments.
     */
    public function index(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $assignments = $patient->checkupTemplateAssignments()
                              ->with(['template', 'assignedBy'])
                              ->orderBy('is_active', 'desc')
                              ->orderBy('assigned_at', 'desc')
                              ->get();

        $availableTemplates = CustomCheckupTemplate::forClinic($patient->clinic_id)
                                                  ->active()
                                                  ->whereNotIn('id', $assignments->where('is_active', true)->pluck('template_id'))
                                                  ->orderBy('medical_condition')
                                                  ->orderBy('name')
                                                  ->get();

        $recommendedTemplates = $patient->recommended_checkup_templates;

        return view('patients.checkup-templates.index', compact('patient', 'assignments', 'availableTemplates', 'recommendedTemplates'));
    }

    /**
     * Assign checkup template to patient.
     */
    public function assign(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'template_id' => 'required|exists:custom_checkup_templates,id',
            'medical_condition' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:500',
        ]);

        $template = CustomCheckupTemplate::findOrFail($request->template_id);

        // Ensure template belongs to the same clinic
        if ($template->clinic_id !== $patient->clinic_id) {
            abort(403, 'Unauthorized access to template.');
        }

        $assignment = PatientCheckupTemplateAssignment::assignTemplate(
            $patient,
            $template,
            Auth::user(),
            $request->medical_condition,
            $request->reason
        );

        return redirect()->route('patients.checkup-templates.index', $patient)
                        ->with('success', "Checkup template '{$template->name}' assigned successfully.");
    }

    /**
     * Assign multiple templates from recommendations.
     */
    public function assignRecommended(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'template_ids' => 'required|array',
            'template_ids.*' => 'exists:custom_checkup_templates,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $assignments = PatientCheckupTemplateAssignment::bulkAssignTemplates(
            $patient,
            $request->template_ids,
            Auth::user(),
            $request->reason
        );

        $count = count($assignments);
        return redirect()->route('patients.checkup-templates.index', $patient)
                        ->with('success', "Successfully assigned {$count} checkup template(s) to patient.");
    }

    /**
     * Update template assignment.
     */
    public function update(Request $request, Patient $patient, PatientCheckupTemplateAssignment $assignment)
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

        return redirect()->route('patients.checkup-templates.index', $patient)
                        ->with('success', 'Checkup template assignment updated successfully.');
    }

    /**
     * Remove template assignment.
     */
    public function destroy(Patient $patient, PatientCheckupTemplateAssignment $assignment)
    {
        $this->authorizePatientAccess($patient);

        // Ensure assignment belongs to this patient
        if ($assignment->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to assignment.');
        }

        $templateName = $assignment->template->name;
        $assignment->deactivate(Auth::user(), 'Manually removed');

        return redirect()->route('patients.checkup-templates.index', $patient)
                        ->with('success', "Checkup template '{$templateName}' removed successfully.");
    }

    /**
     * Toggle template assignment status.
     */
    public function toggle(Patient $patient, PatientCheckupTemplateAssignment $assignment)
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
        $templateName = $assignment->template->name;

        return redirect()->route('patients.checkup-templates.index', $patient)
                        ->with('success', "Checkup template '{$templateName}' {$status} successfully.");
    }

    /**
     * Preview template for patient.
     */
    public function preview(Patient $patient, CustomCheckupTemplate $template)
    {
        $this->authorizePatientAccess($patient);

        // Ensure template belongs to the same clinic
        if ($template->clinic_id !== $patient->clinic_id) {
            abort(403, 'Unauthorized access to template.');
        }

        return response()->json([
            'template' => $template,
            'form_sections' => $template->form_sections,
            'fields_count' => $template->fields_count,
            'sections_count' => $template->sections_count,
            'usage_stats' => $template->usage_stats,
        ]);
    }

    /**
     * Get available templates for AJAX requests.
     */
    public function getAvailableTemplates(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $assignedIds = $patient->activeCheckupTemplateAssignments()->pluck('template_id');
        
        $availableTemplates = CustomCheckupTemplate::forClinic($patient->clinic_id)
                                                  ->active()
                                                  ->whereNotIn('id', $assignedIds)
                                                  ->orderBy('medical_condition')
                                                  ->orderBy('name')
                                                  ->get(['id', 'name', 'description', 'medical_condition', 'specialty', 'checkup_type']);

        return response()->json($availableTemplates);
    }

    /**
     * Get recommended templates based on patient's medical conditions.
     */
    public function getRecommendedTemplates(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $recommendedTemplates = $patient->recommended_checkup_templates;

        return response()->json($recommendedTemplates);
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
