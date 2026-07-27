<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticAftercareIssue;
use App\Models\AestheticAftercareTemplate;
use App\Models\AestheticSession;
use App\Models\AestheticTreatment;
use App\Models\NotificationLog;
use App\Models\Patient;
use App\Services\AestheticDocumentService;
use App\Services\StorageQuotaService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AestheticAftercareIssueController extends Controller
{
    public function store(Request $request, AestheticSession $aestheticSession, AestheticDocumentService $documentService)
    {
        $this->authorizeSession($aestheticSession);

        $validated = $request->validate([
            'aftercare_template_id' => 'required|integer|exists:aesthetic_aftercare_templates,id',
            'treatment_id' => 'nullable|integer|exists:aesthetic_treatments,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (!empty($validated['treatment_id'])) {
            $this->validateTreatmentTenant((int) $validated['treatment_id']);
        }

        $template = AestheticAftercareTemplate::findOrFail($validated['aftercare_template_id']);
        $this->authorizeTemplate($template);

        $patient = $this->resolvePatient($aestheticSession);
        if (!$patient) {
            return back()->withInput()->withErrors([
                'aftercare' => __('Unable to determine the patient linked to this session.'),
            ]);
        }

        DB::transaction(function () use ($validated, $aestheticSession, $template, $patient, $documentService): void {
            $issue = AestheticAftercareIssue::create([
                'patient_id' => $patient->id,
                'session_id' => $aestheticSession->id,
                'treatment_id' => $validated['treatment_id'] ?? $aestheticSession->treatment_id,
                'aftercare_template_id' => $template->id,
                'template_name' => $template->name,
                'template_category' => $template->category,
                'title' => $template->title,
                'instructions_snapshot' => $template->instructions,
                'notes' => $validated['notes'] ?? null,
                'issued_at' => now(),
                'issued_by' => Auth::id(),
            ]);

            $documentService->finalizeAftercareDocument($issue);
        });

        return redirect()->route('aesthetic.sessions.show', $aestheticSession)
            ->with('success', __('Aftercare instructions issued successfully.'));
    }

    public function sendViaWhatsApp(
        AestheticSession $aestheticSession,
        AestheticAftercareIssue $aestheticAftercareIssue,
        WhatsAppService $whatsAppService
    ): JsonResponse {
        $this->authorizeSession($aestheticSession);
        $this->authorizeIssue($aestheticSession, $aestheticAftercareIssue);

        $aestheticAftercareIssue->loadMissing(['patient', 'session', 'patientFile', 'template']);

        $patient = $aestheticAftercareIssue->patient;
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => __('This aftercare issue is not linked to a patient.'),
            ], 422);
        }

        $phoneNumber = $patient->whatsapp_phone ?: $patient->phone;
        if (!$phoneNumber) {
            return response()->json([
                'success' => false,
                'message' => __('The patient does not have a WhatsApp or phone number.'),
            ], 422);
        }

        $message = $this->buildAftercareReminderMessage($aestheticAftercareIssue, $patient);
        $displayName = $aestheticAftercareIssue->patientFile?->original_name
            ?: $aestheticAftercareIssue->pdf_file_name
            ?: ('aftercare-' . $aestheticAftercareIssue->id . '.pdf');

        $temporaryPath = null;
        $result = null;

        try {
            $temporaryPath = $this->resolvePdfLocalPath($aestheticAftercareIssue);
            $whatsAppService->setClinicContext(Auth::user()->clinic_id);

            if ($temporaryPath) {
                $result = $whatsAppService->sendDocument($phoneNumber, $temporaryPath, $displayName, $message);
            } else {
                $result = [
                    'success' => false,
                    'status' => 'pending',
                    'whatsapp_url' => $whatsAppService->getWebUrl($phoneNumber, $this->buildAftercareFallbackMessage($aestheticAftercareIssue, $message)),
                ];
            }
        } finally {
            if ($temporaryPath && str_starts_with($temporaryPath, rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'aftercare_whatsapp_')) {
                @unlink($temporaryPath);
            }
        }

        if (!($result['success'] ?? false) && !isset($result['whatsapp_url'])) {
            $result['whatsapp_url'] = $whatsAppService->getWebUrl($phoneNumber, $this->buildAftercareFallbackMessage($aestheticAftercareIssue, $message));
            $result['status'] = 'pending';
        }

        $status = ($result['success'] ?? false)
            ? NotificationLog::STATUS_SENT
            : (isset($result['whatsapp_url']) ? NotificationLog::STATUS_PENDING : NotificationLog::STATUS_FAILED);

        $this->logReminderAttempt($aestheticAftercareIssue, $patient, $phoneNumber, $message, $status, $result);

        if (($result['success'] ?? false) || isset($result['whatsapp_url'])) {
            return response()->json([
                'success' => true,
                'message' => ($result['success'] ?? false)
                    ? __('Aftercare reminder sent successfully.')
                    : __('Opening WhatsApp to send the aftercare reminder.'),
                'whatsapp_url' => $result['whatsapp_url'] ?? null,
                'auto_open' => isset($result['whatsapp_url']),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? $result['message'] ?? __('Failed to send the aftercare reminder.'),
        ], 422);
    }

    private function authorizeSession(AestheticSession $session): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $tenantId = $user->clinic?->tenant_id;
        if ($tenantId && $session->tenant_id !== $tenantId) {
            abort(403, __('You are not authorized to access this session.'));
        }
    }

    private function authorizeTemplate(AestheticAftercareTemplate $template): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $tenantId = $user->clinic?->tenant_id;
        if ($tenantId && $template->tenant_id !== $tenantId) {
            abort(403, __('You are not authorized to use this aftercare template.'));
        }
    }

    private function validateTreatmentTenant(int $treatmentId): void
    {
        $tenantId = Auth::user()->clinic?->tenant_id;

        $exists = AestheticTreatment::byTenant($tenantId)
            ->where('id', $treatmentId)
            ->exists();

        if (!$exists) {
            abort(403, __('The selected treatment is not available for your clinic.'));
        }
    }

    private function resolvePatient(AestheticSession $session): ?Patient
    {
        if ($session->patient) {
            return $session->patient;
        }

        return $session->patientPackage?->patient;
    }

    private function authorizeIssue(AestheticSession $session, AestheticAftercareIssue $issue): void
    {
        if ((int) $issue->session_id !== (int) $session->id) {
            abort(404);
        }

        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return;
        }

        $tenantId = $user->clinic?->tenant_id;
        if ($tenantId && $issue->tenant_id !== $tenantId) {
            abort(403, __('You are not authorized to access this aftercare issue.'));
        }
    }

    private function buildAftercareReminderMessage(AestheticAftercareIssue $issue, Patient $patient): string
    {
        $clinicName = Auth::user()?->clinic?->name ?? config('app.name');
        $lines = [
            __('Hello :name,', ['name' => $patient->full_name ?? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''))]),
            __('Here is your aftercare reminder for :title.', ['title' => $issue->title ?: $issue->template_name]),
            '',
            $issue->instructions_snapshot,
        ];

        if ($issue->notes) {
            $lines[] = '';
            $lines[] = __('Practitioner notes: :notes', ['notes' => $issue->notes]);
        }

        $lines[] = '';
        $lines[] = __('Clinic: :clinic', ['clinic' => $clinicName]);

        return implode("\n", array_filter($lines, fn ($line) => $line !== null));
    }

    private function buildAftercareFallbackMessage(AestheticAftercareIssue $issue, string $message): string
    {
        $pdfUrl = $issue->pdf_url;
        if (!$pdfUrl || $pdfUrl === '#') {
            return $message;
        }

        return $message . "\n\n" . __('Aftercare PDF: :url', ['url' => $pdfUrl]);
    }

    private function resolvePdfLocalPath(AestheticAftercareIssue $issue): ?string
    {
        $path = $issue->pdf_path ?: $issue->patientFile?->file_path;
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'tenant_')) {
            if (!Storage::disk(StorageQuotaService::SPACES_DISK)->exists($path)) {
                return null;
            }

            $extension = pathinfo($issue->patientFile?->original_name ?: $path, PATHINFO_EXTENSION) ?: 'pdf';
            $temporaryPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'aftercare_whatsapp_' . $issue->id . '_' . uniqid('', true) . '.' . $extension;
            file_put_contents($temporaryPath, Storage::disk(StorageQuotaService::SPACES_DISK)->get($path));

            return $temporaryPath;
        }

        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->path($path);
    }

    private function logReminderAttempt(
        AestheticAftercareIssue $issue,
        Patient $patient,
        string $phoneNumber,
        string $message,
        string $status,
        array $result
    ): void {
        if (!Schema::hasTable('notification_logs')) {
            return;
        }

        NotificationLog::withoutGlobalScopes()->create([
            'clinic_id' => Auth::user()->clinic_id,
            'patient_id' => $patient->id,
            'type' => NotificationLog::TYPE_FOLLOW_UP,
            'channel' => 'whatsapp',
            'recipient' => $phoneNumber,
            'message' => $message,
            'status' => $status,
            'error_message' => $result['error'] ?? null,
            'external_id' => $result['message_id'] ?? $result['message_sid'] ?? null,
            'notifiable_type' => AestheticAftercareIssue::class,
            'notifiable_id' => $issue->id,
            'metadata' => [
                'source' => 'aesthetic_aftercare',
                'session_id' => $issue->session_id,
                'pdf_path' => $issue->pdf_path,
                'whatsapp_url' => $result['whatsapp_url'] ?? null,
            ],
            'sent_at' => $status === NotificationLog::STATUS_SENT ? now() : null,
        ]);
    }
}