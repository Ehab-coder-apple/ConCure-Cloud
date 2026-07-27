<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticSession;
use App\Models\AestheticTreatment;
use App\Models\ConsentForm;
use App\Models\Patient;
use App\Services\AestheticDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AestheticConsentController extends Controller
{
    public function store(Request $request, AestheticSession $aestheticSession, AestheticDocumentService $documentService)
    {
        $this->authorizeSession($aestheticSession);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:20000',
            'signer_name' => 'nullable|string|max:255',
            'treatment_id' => 'nullable|integer|exists:aesthetic_treatments,id',
            'signature_data' => 'required|string',
        ]);

        if (!$this->isDataUrlImage($validated['signature_data'])) {
            return back()->withInput()->withErrors([
                'signature_data' => __('Please provide a valid electronic signature.'),
            ]);
        }

        if (!empty($validated['treatment_id'])) {
            $this->validateTreatmentTenant((int) $validated['treatment_id']);
        }

        $patient = $this->resolvePatient($aestheticSession);
        if (!$patient) {
            return back()->withInput()->withErrors([
                'consent' => __('Unable to determine the patient linked to this session.'),
            ]);
        }

        DB::transaction(function () use ($validated, $aestheticSession, $patient, $documentService): void {
            $consentForm = ConsentForm::create([
                'patient_id' => $patient->id,
                'session_id' => $aestheticSession->id,
                'treatment_id' => $validated['treatment_id'] ?? $aestheticSession->treatment_id,
                'title' => $validated['title'],
                'body' => $validated['body'],
                'signature_data' => $validated['signature_data'],
                'signer_name' => $validated['signer_name'] ?? $patient->full_name ?? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
                'signed_at' => now(),
                'created_by' => Auth::id(),
            ]);

            $documentService->finalizeConsentDocument($consentForm);
        });

        return redirect()->route('aesthetic.sessions.show', $aestheticSession)
            ->with('success', __('Consent form captured and archived successfully.'));
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

    private function isDataUrlImage(string $value): bool
    {
        return str_starts_with($value, 'data:image/') && str_contains($value, ';base64,');
    }
}