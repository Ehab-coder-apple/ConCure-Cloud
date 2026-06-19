<?php

namespace App\Services;

use App\Models\AestheticAftercareIssue;
use App\Models\Clinic;
use App\Models\ConsentForm;
use App\Models\Patient;
use App\Models\PatientFile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AestheticDocumentService
{
    public function finalizeConsentDocument(ConsentForm $consentForm): ConsentForm
    {
        $consentForm->loadMissing(['patient', 'session.patientPackage.patient', 'treatment', 'creator']);

        $patient = $consentForm->patient;
        if (!$patient || !$patient->clinic_id) {
            throw new \RuntimeException('Consent form patient or clinic context is missing.');
        }

        $clinic = Clinic::find($patient->clinic_id);
        $originalName = 'aesthetic-consent-' . ($patient->patient_id ?: $patient->id) . '-' . now()->format('Ymd_His') . '.pdf';
        $pdfBinary = Pdf::loadView('aesthetic.consents.pdf', [
            'consentForm' => $consentForm,
            'patient' => $patient,
            'clinic' => $clinic,
        ])->setPaper('a4')->output();

        $stored = $this->storePdf($patient, $originalName, $pdfBinary);

        $patientFile = PatientFile::create([
            'patient_id' => $patient->id,
            'original_name' => $originalName,
            'file_name' => $stored['file_name'],
            'file_path' => $stored['path'],
            'file_type' => 'application/pdf',
            'file_size' => $stored['file_size'],
            'mime_type' => 'application/pdf',
            'category' => 'medical_report',
            'description' => __('Aesthetic consent form for session #:session', ['session' => $consentForm->session?->session_number ?? '—']),
            'uploaded_by' => $consentForm->created_by,
        ]);

        $consentForm->update([
            'patient_file_id' => $patientFile->id,
            'pdf_file_name' => $stored['file_name'],
            'pdf_path' => $stored['path'],
            'pdf_file_size' => $stored['file_size'],
        ]);

        return $consentForm->fresh(['patientFile', 'session', 'treatment', 'creator']);
    }

    public function finalizeAftercareDocument(AestheticAftercareIssue $issue): AestheticAftercareIssue
    {
        $issue->loadMissing(['patient', 'session.patientPackage.patient', 'template', 'issuer', 'treatment']);

        $patient = $issue->patient;
        if (!$patient || !$patient->clinic_id) {
            throw new \RuntimeException('Aftercare issue patient or clinic context is missing.');
        }

        $clinic = Clinic::find($patient->clinic_id);
        $originalName = 'aesthetic-aftercare-' . ($patient->patient_id ?: $patient->id) . '-' . now()->format('Ymd_His') . '.pdf';
        $pdfBinary = Pdf::loadView('aesthetic.aftercare.pdf', [
            'issue' => $issue,
            'patient' => $patient,
            'clinic' => $clinic,
        ])->setPaper('a4')->output();

        $stored = $this->storePdf($patient, $originalName, $pdfBinary);

        $patientFile = PatientFile::create([
            'patient_id' => $patient->id,
            'original_name' => $originalName,
            'file_name' => $stored['file_name'],
            'file_path' => $stored['path'],
            'file_type' => 'application/pdf',
            'file_size' => $stored['file_size'],
            'mime_type' => 'application/pdf',
            'category' => 'medical_report',
            'description' => __('Aesthetic aftercare instructions for session #:session', ['session' => $issue->session?->session_number ?? '—']),
            'uploaded_by' => $issue->issued_by,
        ]);

        $issue->update([
            'patient_file_id' => $patientFile->id,
            'pdf_file_name' => $stored['file_name'],
            'pdf_path' => $stored['path'],
            'pdf_file_size' => $stored['file_size'],
        ]);

        return $issue->fresh(['patientFile', 'session', 'template', 'issuer']);
    }

    private function storePdf(Patient $patient, string $originalName, string $pdfBinary): array
    {
        $clinicId = (int) $patient->clinic_id;
        $fileName = Str::uuid()->toString() . '.pdf';

        if ($this->shouldUseSpaces()) {
            $path = StorageQuotaService::getTenantStoragePath($clinicId, 'documents') . '/' . $fileName;
            Storage::disk(StorageQuotaService::SPACES_DISK)->put($path, $pdfBinary, ['visibility' => 'private']);
        } else {
            $path = 'documents/tenant_' . $clinicId . '/' . $fileName;
            Storage::disk('public')->put($path, $pdfBinary);
        }

        $fileSize = strlen($pdfBinary);
        app(StorageQuotaService::class)->incrementUsage($clinicId, $fileSize);

        return [
            'path' => $path,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_size' => $fileSize,
        ];
    }

    private function shouldUseSpaces(): bool
    {
        $disk = config('filesystems.disks.' . StorageQuotaService::SPACES_DISK, []);

        return !empty($disk['key']) && !empty($disk['secret']) && !empty($disk['bucket']) && !empty($disk['endpoint']);
    }
}