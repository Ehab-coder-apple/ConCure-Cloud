<?php

namespace App\Services;

use App\Models\CommunicationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Mail\Mailable;

class EmailService
{
    /**
     * Send email with optional attachment
     */
    public function sendEmail(
        string $toEmail, 
        string $subject, 
        string $message, 
        ?string $attachmentPath = null, 
        array $metadata = []
    ): array {
        try {
            // Validate email
            if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'error' => 'Invalid email address format',
                    'status' => 'failed',
                ];
            }

            // Create a simple mailable
            $mailable = new \App\Mail\GenericEmail($subject, $message);

            // Add attachment if provided
            if ($attachmentPath && Storage::exists($attachmentPath)) {
                $mailable->attach(Storage::path($attachmentPath), [
                    'as' => basename($attachmentPath),
                    'mime' => Storage::mimeType($attachmentPath),
                ]);
            }

            // Send email
            Mail::to($toEmail)->send($mailable);

            return [
                'success' => true,
                'status' => 'sent',
                'message' => 'Email sent successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Email send failed', [
                'email' => $toEmail,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed',
            ];
        }
    }

    /**
     * Log communication attempt
     */
    public function logCommunication(
        int $patientId,
        int $clinicId,
        string $recipient,
        string $message,
        ?string $subject = null,
        ?string $attachmentPath = null,
        string $status = 'pending',
        ?string $errorMessage = null,
        ?string $externalId = null,
        array $metadata = [],
        ?int $sentBy = null
    ): CommunicationLog {
        return CommunicationLog::create([
            'patient_id' => $patientId,
            'clinic_id' => $clinicId,
            'type' => 'email',
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'attachment_path' => $attachmentPath,
            'status' => $status,
            'error_message' => $errorMessage,
            'external_id' => $externalId,
            'metadata' => $metadata,
            'sent_by' => $sentBy ?: auth()->id(),
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }

    /**
     * Check if email is configured
     */
    public function isConfigured(): bool
    {
        return config('mail.default') !== null && 
               config('mail.mailers.' . config('mail.default') . '.host') !== null;
    }

    /**
     * Validate email address
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Send lab request via email
     */
    public function sendLabRequest(
        string $toEmail,
        \App\Models\LabRequest $labRequest,
        ?string $pdfPath = null
    ): array {
        try {
            $subject = "Lab Request #{$labRequest->request_number} - {$labRequest->patient->full_name}";
            
            $message = "Dear Laboratory Team,\n\n";
            $message .= "Please find attached the lab request for the following patient:\n\n";
            $message .= "Patient: {$labRequest->patient->full_name}\n";
            $message .= "Patient ID: {$labRequest->patient->patient_id}\n";
            $message .= "Request Number: {$labRequest->request_number}\n";
            $message .= "Priority: {$labRequest->priority_display}\n";
            $message .= "Due Date: " . ($labRequest->due_date ? $labRequest->due_date->format('M d, Y') : 'Not specified') . "\n\n";
            
            if ($labRequest->clinical_notes) {
                $message .= "Clinical Notes: {$labRequest->clinical_notes}\n\n";
            }
            
            $message .= "Tests Required:\n";
            foreach ($labRequest->tests as $index => $test) {
                $message .= ($index + 1) . ". {$test->test_name}";
                if ($test->instructions) {
                    $message .= " - {$test->instructions}";
                }
                $message .= "\n";
            }
            
            $message .= "\nPlease process this request and send the results back to this email address.\n\n";
            $message .= "Thank you,\n";
            $message .= "Dr. {$labRequest->doctor->first_name} {$labRequest->doctor->last_name}\n";
            $message .= auth()->user()->clinic->name ?? 'ConCure Clinic';

            return $this->sendEmail($toEmail, $subject, $message, $pdfPath);

        } catch (\Exception $e) {
            Log::error('Lab request email send failed', [
                'lab_request_id' => $labRequest->id,
                'email' => $toEmail,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed',
            ];
        }
    }
}
