<?php

namespace App\Http\Controllers;

use App\Models\LabRequest;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Services\WhatsAppService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class LabRequestCommunicationController extends Controller
{
    protected $whatsappService;
    protected $emailService;

    public function __construct(WhatsAppService $whatsappService, EmailService $emailService)
    {
        $this->whatsappService = $whatsappService;
        $this->emailService = $emailService;
    }

    /**
     * Send lab request via WhatsApp
     */
    public function sendViaWhatsApp(Request $request, LabRequest $labRequest)
    {
        $user = auth()->user();

        // Ensure user can only send lab requests from their clinic
        if ($labRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to lab request.');
        }

        $request->validate([
            'phone_number' => 'nullable|string',
            'message' => 'nullable|string',
        ]);

        // Use lab request's WhatsApp number, request parameter, or clinic's default WhatsApp number
        $phoneNumber = $request->phone_number ?: $labRequest->lab_whatsapp ?: $this->whatsappService->getClinicWhatsAppNumber();

        if (!$phoneNumber) {
            return response()->json([
                'success' => false,
                'message' => 'No WhatsApp number available. Please add a WhatsApp number to the lab request, provide one, or configure a default WhatsApp number in clinic settings.',
            ], 400);
        }

        try {
            // Generate PDF of lab request
            $pdfPath = $this->generateLabRequestPDF($labRequest);

            // Prepare WhatsApp message
            $message = $request->message ?: $this->getDefaultWhatsAppMessage($labRequest);

            // Send via WhatsApp
            $result = $this->whatsappService->sendMessage(
                $phoneNumber,
                $message,
                $pdfPath
            );

            // Log communication
            $this->whatsappService->logCommunication(
                $labRequest->patient_id,
                $user->clinic_id,
                $phoneNumber,
                $message,
                "Lab Request #{$labRequest->request_number}",
                $pdfPath,
                $result['status'],
                $result['success'] ? null : $result['error'],
                $result['message_id'] ?? null,
                $result
            );

            // Update lab request
            if ($result['success']) {
                $labRequest->markAsSent('whatsapp', 'Sent via WhatsApp to ' . $phoneNumber);
            }

            if ($result['success']) {
                if (isset($result['whatsapp_url'])) {
                    return response()->json([
                        'success' => true,
                        'message' => 'WhatsApp URL generated successfully',
                        'whatsapp_url' => $result['whatsapp_url'],
                        'pdf_url' => Storage::url($pdfPath),
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Lab request sent via WhatsApp successfully',
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', [
                'lab_request_id' => $labRequest->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send lab request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send lab request via Email
     */
    public function sendViaEmail(Request $request, LabRequest $labRequest)
    {
        $user = auth()->user();

        // Ensure user can only send lab requests from their clinic
        if ($labRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to lab request.');
        }

        $request->validate([
            'email' => 'nullable|email',
            'subject' => 'nullable|string',
            'message' => 'nullable|string',
        ]);

        // Use lab request's email if not provided in request
        $email = $request->email ?: $labRequest->lab_email;

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'No email address available. Please add an email address to the lab request or provide one.',
            ], 400);
        }

        try {
            // Generate PDF of lab request
            $pdfPath = $this->generateLabRequestPDF($labRequest);

            // Send via Email
            $result = $this->emailService->sendLabRequest(
                $email,
                $labRequest,
                $pdfPath
            );

            // Log communication
            $this->emailService->logCommunication(
                $labRequest->patient_id,
                $user->clinic_id,
                $email,
                $request->message ?: 'Lab request sent via email',
                $request->subject ?: "Lab Request #{$labRequest->request_number}",
                $pdfPath,
                $result['status'],
                $result['success'] ? null : $result['error']
            );

            // Update lab request
            if ($result['success']) {
                $labRequest->markAsSent('email', 'Sent via email to ' . $email);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Email send failed', [
                'lab_request_id' => $labRequest->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send lab request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload lab result file
     */
    public function uploadResult(Request $request, LabRequest $labRequest)
    {
        $user = auth()->user();

        // Ensure user can only upload results for lab requests from their clinic
        if ($labRequest->patient->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to lab request.');
        }

        $request->validate([
            'result_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $file = $request->file('result_file');
            
            // Generate unique filename
            $filename = 'lab_result_' . $labRequest->request_number . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs("patients/{$labRequest->patient_id}/lab_results", $filename, 'public');

            // Add result file to lab request
            $labRequest->addResultFile($path, $user->id);

            // Also add to patient files
            PatientFile::create([
                'patient_id' => $labRequest->patient_id,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'category' => 'lab_result',
                'description' => $request->description ?: "Lab result for request #{$labRequest->request_number}",
                'uploaded_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lab result uploaded successfully',
                'file_url' => Storage::url($path),
            ]);

        } catch (\Exception $e) {
            Log::error('Lab result upload failed', [
                'lab_request_id' => $labRequest->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload lab result: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF for lab request
     */
    protected function generateLabRequestPDF(LabRequest $labRequest): string
    {
        $labRequest->load(['patient', 'doctor', 'tests']);

        // Split tests into chunks of 6 for pagination
        $testChunks = $labRequest->tests->chunk(6);
        $totalPages = $testChunks->count();

        if ($totalPages <= 1) {
            // Single page - use existing template
            $pdf = Pdf::loadView('recommendations.lab-request-pdf', compact('labRequest'));
        } else {
            // Multiple pages - generate separate PDFs and merge
            $pdfs = [];

            foreach ($testChunks as $pageIndex => $testsChunk) {
                $pageNumber = $pageIndex + 1;
                $labRequestPage = clone $labRequest;
                $labRequestPage->setRelation('tests', $testsChunk);

                $pdf = Pdf::loadView('recommendations.lab-request-pdf', [
                    'labRequest' => $labRequestPage,
                    'pageNumber' => $pageNumber,
                    'totalPages' => $totalPages,
                    'isMultiPage' => true
                ]);

                $pdfs[] = $pdf->output();
            }

            // For now, return the first page (we'll implement PDF merging later)
            $pdf = Pdf::loadView('recommendations.lab-request-pdf', [
                'labRequest' => $labRequest,
                'testChunks' => $testChunks,
                'totalPages' => $totalPages,
                'isMultiPage' => true
            ]);
        }

        $filename = 'lab_request_' . $labRequest->request_number . '_' . time() . '.pdf';
        $path = "temp/lab_requests/{$filename}";

        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Get default WhatsApp message
     */
    protected function getDefaultWhatsAppMessage(LabRequest $labRequest): string
    {
        $message = "🏥 *Lab Request #{$labRequest->request_number}*\n\n";
        $message .= "📋 *Patient:* {$labRequest->patient->full_name}\n";
        $message .= "🆔 *Patient ID:* {$labRequest->patient->patient_id}\n";
        $message .= "⚡ *Priority:* {$labRequest->priority_display}\n";
        
        if ($labRequest->due_date) {
            $message .= "📅 *Due Date:* {$labRequest->due_date->format('M d, Y')}\n";
        }
        
        $message .= "\n🧪 *Tests Required:*\n";
        foreach ($labRequest->tests as $index => $test) {
            $message .= ($index + 1) . ". {$test->test_name}\n";
        }
        
        if ($labRequest->clinical_notes) {
            $message .= "\n📝 *Clinical Notes:*\n{$labRequest->clinical_notes}\n";
        }
        
        $message .= "\n👨‍⚕️ *Requested by:* Dr. {$labRequest->doctor->first_name} {$labRequest->doctor->last_name}\n";
        $message .= "🏥 *Clinic:* " . (auth()->user()->clinic->name ?? 'ConCure Clinic') . "\n\n";
        $message .= "Please process this request and send the results back.\n\n";
        $message .= "📱 Generated by ConCure Clinic Management System";

        return $message;
    }
}
