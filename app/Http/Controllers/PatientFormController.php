<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\FormTemplate;
use App\Models\Patient;
use App\Models\PatientForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


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
     * Show fill page for an assignment
     */
    public function fill(Patient $patient, PatientForm $patientForm)
    {
        $this->authorizePatientAccess($patient);
        $user = Auth::user();
        if (!$user->canFillForms() && !$user->canAssignForms() && !$user->canManageFormTemplates()) {
            abort(403, 'You do not have permission to fill forms.');
        }

        if ((int) $patientForm->patient_id !== (int) $patient->id) {
            abort(403, 'Unauthorized access to patient form.');
        }

        if ($patientForm->isCompleted()) {
            return redirect()->route('patients.forms.show', [$patient, $patientForm])
                ->with('info', 'This form is already completed.');
        }

        return view('patients.forms.fill', [
            'patient' => $patient,
            'assignment' => $patientForm->load(['template', 'assignedBy', 'filledBy']),
        ]);
    }

    /**
     * Submit filled data (save progress or complete)
     */
    public function submitFill(Request $request, Patient $patient, PatientForm $patientForm)
    {
        $this->authorizePatientAccess($patient);
        $user = Auth::user();
        if (!$user->canFillForms() && !$user->canAssignForms() && !$user->canManageFormTemplates()) {
            abort(403, 'You do not have permission to fill forms.');
        }

        if ((int) $patientForm->patient_id !== (int) $patient->id) {
            abort(403, 'Unauthorized access to patient form.');
        }

        if ($patientForm->isCompleted()) {
            return redirect()->route('patients.forms.show', [$patient, $patientForm])
                ->with('info', 'This form is already completed.');
        }

        $data = $request->validate([
            'content' => 'nullable|string',
            'action' => 'required|string|in:save,complete',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:' . config('app.concure.max_file_size', 10240),
        ]);

        // Persist data
        $payload = [
            'content' => $data['content'] ?? '',
        ];

        // Handle optional attachment upload (allowed in both save and complete flows)
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $disk = Storage::disk('public');
            $dir = $patientForm->storageDir() . '/attachments';
            $originalName = $attachment->getClientOriginalName();
            $filename = time() . '_' . $originalName;
            $path = $attachment->storeAs($dir, $filename, 'public');

            // Delete previous attachment if any
            if ($patientForm->attachment_path && $patientForm->attachment_path !== $path && $disk->exists($patientForm->attachment_path)) {
                $disk->delete($patientForm->attachment_path);
            }
            $patientForm->attachment_path = $path;
            $patientForm->attachment_name = $originalName;
            $patientForm->attachment_mime = $attachment->getClientMimeType() ?: $attachment->getMimeType();
            $patientForm->attachment_size = $attachment->getSize();
            $patientForm->save();
        }

        if ($data['action'] === 'complete') {
            $patientForm->markCompleted($user->id, $payload);

            // Generate and store immutable PDF snapshot
            try {
                $this->generateAndStorePdfSnapshot($patient, $patientForm);
            } catch (\Throwable $e) {
                \Log::error('Failed to generate form PDF snapshot', [
                    'assignment_id' => $patientForm->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->route('patients.forms.show', [$patient, $patientForm])
                ->with('success', 'Form marked as completed.');
        }

        // Save progress (in progress)
        $patientForm->form_data = $payload;
        $patientForm->markInProgress();
        return redirect()->route('patients.forms.show', [$patient, $patientForm])
            ->with('success', 'Progress saved.');
    }

    /**
     * Export a completed form as PDF
     */
    public function pdf(Patient $patient, PatientForm $patientForm)
    {
        $this->authorizePatientAccess($patient);
        $user = Auth::user();
        if (!$user->canViewPatientForms() && !$user->canManageFormTemplates() && !$user->canFillForms() && !$user->canAssignForms()) {
            abort(403, 'Insufficient permissions to export forms.');
        }

        if ((int) $patientForm->patient_id !== (int) $patient->id) {
            abort(403, 'Unauthorized access to patient form.');
        }

        if (!$patientForm->isCompleted()) {
            return redirect()->route('patients.forms.show', [$patient, $patientForm])
                ->with('warning', 'Only completed forms can be exported as PDF.');
        }

        $patientForm->load(['template', 'assignedBy', 'filledBy']);

        $pdf = Pdf::loadView('patients.forms.pdf', [
            'patient' => $patient,
            'assignment' => $patientForm,
        ])->setPaper('a4');

        $filename = 'PatientForm-' . str_replace(' ', '-', ($patientForm->template->name ?? 'Form')) . '-' . now()->format('Ymd_His') . '.pdf';
        if (request()->boolean('open')) {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }

    /**
     * Download/stream the uploaded attachment (if any)
     */
    public function attachment(Patient $patient, PatientForm $patientForm)
    {
        $this->authorizePatientAccess($patient);
        $user = Auth::user();
        if (!$user->canViewPatientForms() && !$user->canFillForms() && !$user->canAssignForms()) {
            abort(403, 'You do not have permission to view attachments.');
        }
        if ((int) $patientForm->patient_id !== (int) $patient->id) {
            abort(403, 'Unauthorized access to patient form.');
        }
        if (empty($patientForm->attachment_path)) {
            return back()->with('error', __('No attachment for this form.'));
        }

        $disk = Storage::disk('public');
        $path = $patientForm->attachment_path;
        if (!$disk->exists($path)) {
            return back()->with('error', __('Attachment file not found.'));
        }

        $absolutePath = $disk->path($path);
        $filename = $patientForm->attachment_name ?: basename($path);
        $mime = File::mimeType($absolutePath) ?: 'application/octet-stream';

        try {
            return response()->streamDownload(function () use ($absolutePath) {
                $stream = fopen($absolutePath, 'rb');
                while (!feof($stream)) {
                    echo fread($stream, 1024 * 1024);
                    @ob_flush();
                    flush();
                }
                fclose($stream);
            }, $filename, [
                'Content-Type' => $mime,
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Attachment stream failed', [
                'assignment_id' => $patientForm->id,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return $disk->download($path, $filename);
        }
    }

    /**
     * Generate and store a PDF snapshot for the completed form
     */
    protected function generateAndStorePdfSnapshot(Patient $patient, PatientForm $patientForm): void
    {
        // Ensure relationships for the PDF view
        $patientForm->load(['template', 'assignedBy', 'filledBy']);

        $pdf = Pdf::loadView('patients.forms.pdf', [
            'patient' => $patient,
            'assignment' => $patientForm,
        ])->setPaper('a4');

        $content = $pdf->output();
        $disk = Storage::disk('public');
        $dir = $patientForm->storageDir() . '/pdf';
        $filename = 'PatientForm-' . Str::slug($patientForm->template->name ?? 'Form') . '-' . now()->format('Ymd_His') . '.pdf';
        $path = $dir . '/' . $filename;

        $disk->put($path, $content, 'public');
        $patientForm->pdf_path = $path;
        $patientForm->pdf_generated_at = now();
        $patientForm->save();
    }

    /**
     * Stream the stored PDF snapshot, if available
     */
    public function pdfSnapshot(Patient $patient, PatientForm $patientForm)
    {
        $this->authorizePatientAccess($patient);
        $user = Auth::user();
        if (!$user->canViewPatientForms() && !$user->canManageFormTemplates() && !$user->canFillForms() && !$user->canAssignForms()) {
            abort(403, 'Insufficient permissions to view PDF snapshot.');
        }
        if ((int) $patientForm->patient_id !== (int) $patient->id) {
            abort(403, 'Unauthorized access to patient form.');
        }
        if (empty($patientForm->pdf_path)) {
            return redirect()->route('patients.forms.show', [$patient, $patientForm])
                ->with('warning', __('No stored PDF snapshot for this form yet.'));
        }

        $disk = Storage::disk('public');
        $path = $patientForm->pdf_path;
        if (!$disk->exists($path)) {
            return redirect()->route('patients.forms.show', [$patient, $patientForm])
                ->with('error', __('Stored PDF snapshot file not found.'));
        }

        $absolutePath = $disk->path($path);
        $filename = basename($path);
        $mime = File::mimeType($absolutePath) ?: 'application/pdf';

        try {
            return response()->streamDownload(function () use ($absolutePath) {
                $stream = fopen($absolutePath, 'rb');
                while (!feof($stream)) {
                    echo fread($stream, 1024 * 1024);
                    @ob_flush();
                    flush();
                }
                fclose($stream);
            }, $filename, [
                'Content-Type' => $mime,
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
        } catch (\Throwable $e) {
            \Log::error('PDF snapshot stream failed', [
                'assignment_id' => $patientForm->id,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return $disk->download($path, $filename);
        }
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

