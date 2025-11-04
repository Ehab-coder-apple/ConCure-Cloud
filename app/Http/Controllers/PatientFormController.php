<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\Patient;
use App\Models\PatientForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientFormController extends Controller
{
    /**
     * List all forms assigned to a patient
     */
    public function index(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $user = Auth::user();
        if (!$user->canViewPatientForms() && !$user->canAssignForms() && !$user->canFillForms() && !$user->canManageFormTemplates()) {
            abort(403, 'Insufficient permissions to view patient forms.');
        }

        $status = $request->get('status');
        $formsQuery = PatientForm::query()
            ->where('patient_id', $patient->id)
            ->where('clinic_id', $patient->clinic_id)
            ->with(['template', 'assignedBy', 'filledBy'])
            ->orderByDesc('assigned_at');

        if ($status) {
            $formsQuery->where('status', $status);
        }

        $forms = $formsQuery->paginate(15);

        return view('patients.forms.index', [
            'patient' => $patient,
            'forms' => $forms,
            'status' => $status,
        ]);
    }

    /**
     * Show assign form page
     */
    public function create(Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $user = Auth::user();
        if (!$user->canAssignForms()) {
            abort(403, 'You do not have permission to assign forms.');
        }

        $templates = FormTemplate::forClinic($patient->clinic_id)->active()->orderBy('name')->get();

        return view('patients.forms.create', [
            'patient' => $patient,
            'templates' => $templates,
        ]);
    }

    /**
     * Handle assignment submission
     */
    public function store(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);
        $user = Auth::user();
        if (!$user->canAssignForms()) {
            abort(403, 'You do not have permission to assign forms.');
        }

        $request->validate([
            'template_id' => 'required|exists:form_templates,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $template = FormTemplate::findOrFail($request->template_id);

        // Ensure template belongs to the same clinic as the patient
        if ((int) $template->clinic_id !== (int) $patient->clinic_id) {
            abort(403, 'Unauthorized access to template.');
        }

        DB::transaction(function () use ($patient, $template, $user, $request) {
            PatientForm::create([
                'clinic_id' => $patient->clinic_id,
                'patient_id' => $patient->id,
                'form_template_id' => $template->id,
                'assigned_by_user_id' => $user->id,
                'assigned_at' => now(),
                'status' => PatientForm::STATUS_ASSIGNED,
                'notes' => $request->notes,
            ]);
        });

        return redirect()->route('patients.forms.index', $patient)
            ->with('success', "Form '{$template->name}' assigned to {$patient->full_name}.");
    }

    /**
     * View single assignment details
     */
    public function show(Patient $patient, PatientForm $patientForm)
    {
        $this->authorizePatientAccess($patient);
        $user = Auth::user();
        if (!$user->canViewPatientForms() && !$user->canFillForms() && !$user->canAssignForms() && !$user->canManageFormTemplates()) {
            abort(403, 'Insufficient permissions to view this form assignment.');
        }

        if ((int) $patientForm->patient_id !== (int) $patient->id) {
            abort(403, 'Unauthorized access to patient form.');
        }

        $patientForm->load(['template', 'assignedBy', 'filledBy']);

        return view('patients.forms.show', [
            'patient' => $patient,
            'assignment' => $patientForm,
        ]);
    }

    /**
     * Unassign/remove an assignment
     */
    public function destroy(Patient $patient, PatientForm $patientForm)
    {
        $this->authorizePatientAccess($patient);
        $user = Auth::user();
        if (!$user->canAssignForms()) {
            abort(403, 'You do not have permission to unassign forms.');
        }

        if ((int) $patientForm->patient_id !== (int) $patient->id) {
            abort(403, 'Unauthorized access to patient form.');
        }

        $templateName = optional($patientForm->template)->name ?? 'Form';
        $patientForm->delete();

        return redirect()->route('patients.forms.index', $patient)
            ->with('success', "Removed '{$templateName}' from patient.");
    }

    /**
     * Authorization helper similar to other patient controllers
     */
    private function authorizePatientAccess(Patient $patient): void
    {
        // DEVELOPMENT MODE: allow if debug bypass is enabled
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return;
        }

        $user = Auth::user();

        // Ensure same clinic
        if ((int) $patient->clinic_id !== (int) $user->clinic_id) {
            abort(403, 'Unauthorized access to patient.');
        }

        // Permission/role based access similar to other controllers
        if (!$user->hasPermission('patients_view') &&
            !$user->canManagePatients() &&
            !in_array($user->role, ['doctor', 'admin', 'nurse'])) {
            abort(403, 'Insufficient permissions to view patients.');
        }
    }
}

