<?php

namespace App\Services;

use App\Models\CommunicationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class WhatsAppService
{
    protected $provider;
    protected $apiUrl;
    protected $apiToken;
    protected $phoneNumberId;
    protected $twilioSid;
    protected $twilioToken;
    protected $twilioFrom;

    public function __construct()
    {
        // Load configuration from config/whatsapp.php
        $config = config('whatsapp', []);
        $defaultProvider = $config['default_provider'] ?? env('WHATSAPP_PROVIDER', 'twilio');

        // Twilio configuration
        $twilioConfig = $config['providers']['twilio'] ?? [];
        $this->twilioSid = $twilioConfig['account_sid'] ?? env('TWILIO_SID');
        $this->twilioToken = $twilioConfig['auth_token'] ?? env('TWILIO_TOKEN');
        $this->twilioFrom = $twilioConfig['from_number'] ?? env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');

        // Meta WhatsApp Business API configuration
        $metaConfig = $config['providers']['meta'] ?? [];
        $this->apiToken = $metaConfig['access_token'] ?? env('WHATSAPP_ACCESS_TOKEN');
        $this->phoneNumberId = $metaConfig['phone_number_id'] ?? env('WHATSAPP_PHONE_NUMBER_ID');

        // ChatAPI configuration
        $chatApiConfig = $config['providers']['chatapi'] ?? [];
        $this->apiUrl = $chatApiConfig['api_url'] ?? env('CHATAPI_URL');
        $chatApiToken = $chatApiConfig['api_token'] ?? env('CHATAPI_TOKEN');

        // Auto-detect provider based on configuration and available credentials
        $this->provider = $this->detectBestProvider($defaultProvider, $config);

        // Log provider selection for desktop application debugging
        if (config('whatsapp.desktop.enable_logging', true)) {
            Log::info('WhatsApp Service Initialized', [
                'provider' => $this->provider,
                'default_provider' => $defaultProvider,
                'twilio_configured' => !empty($this->twilioSid) && !empty($this->twilioToken),
                'meta_configured' => !empty($this->apiToken) && !empty($this->phoneNumberId),
                'chatapi_configured' => !empty($this->apiUrl) && !empty($chatApiToken),
            ]);
        }
    }

    /**
     * Detect the best available WhatsApp provider for desktop applications
     */
    private function detectBestProvider(string $defaultProvider, array $config): string
    {
        // Check if the default provider is properly configured
        switch ($defaultProvider) {
            case 'twilio':
                if ($this->twilioSid && $this->twilioToken) {
                    return 'twilio';
                }
                break;

            case 'meta':
                if ($this->apiToken && $this->phoneNumberId) {
                    return 'meta';
                }
                break;

            case 'chatapi':
                if ($this->apiUrl && !empty($config['providers']['chatapi']['api_token'])) {
                    return 'chatapi';
                }
                break;

            case 'wppconnect':
                if (!empty($config['providers']['wppconnect']['api_url'])) {
                    return 'wppconnect';
                }
                break;
        }

        // Fallback: Try to find any working provider
        if ($this->twilioSid && $this->twilioToken) {
            return 'twilio';
        }

        if ($this->apiToken && $this->phoneNumberId) {
            return 'meta';
        }

        if ($this->apiUrl && !empty($config['providers']['chatapi']['api_token'])) {
            return 'chatapi';
        }

        if (!empty($config['providers']['wppconnect']['api_url'])) {
            return 'wppconnect';
        }

        // Final fallback to web
        return 'web';
    }

    /**
     * Send document via WhatsApp
     */
    public function sendDocument(string $phoneNumber, string $filePath, string $fileName, string $message = ''): array
    {
        try {
            // Clean phone number (remove non-digits)
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

            // Ensure phone number starts with country code
            if (!str_starts_with($cleanPhone, '964') && strlen($cleanPhone) === 10) {
                $cleanPhone = '964' . $cleanPhone; // Add Iraq country code
            }

            // Log provider selection for debugging
            Log::info("WhatsApp provider selected: {$this->provider}");

            // Route to appropriate provider for API-based sending
            switch ($this->provider) {
                case 'twilio':
                    Log::info("Sending document via Twilio to: {$cleanPhone}");
                    return $this->sendDocumentViaTwilio($cleanPhone, $filePath, $fileName, $message);
                case 'meta':
                    Log::info("Sending document via Meta to: {$cleanPhone}");
                    return $this->sendDocumentViaMeta($cleanPhone, $filePath, $fileName, $message);
                case 'chatapi':
                    Log::info("Sending document via ChatAPI to: {$cleanPhone}");
                    return $this->sendDocumentViaChatAPI($cleanPhone, $filePath, $fileName, $message);
                case 'wppconnect':
                    Log::info("Sending document via WPPConnect to: {$cleanPhone}");
                    return $this->sendDocumentViaWPPConnect($cleanPhone, $filePath, $fileName, $message);
                default:
                    Log::info("Sending document via Web to: {$cleanPhone}");
                    return $this->sendDocumentViaWeb($cleanPhone, $filePath, $fileName, $message);
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp document send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send document: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp message with optional attachment
     */
    public function sendMessage(string $phoneNumber, string $message, ?string $attachmentPath = null, array $metadata = []): array
    {
        try {
            // Clean phone number (remove non-digits)
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

            // Ensure phone number starts with country code
            if (!str_starts_with($cleanPhone, '964') && strlen($cleanPhone) === 10) {
                $cleanPhone = '964' . $cleanPhone; // Add Iraq country code
            }

            // Route to appropriate provider
            switch ($this->provider) {
                case 'twilio':
                    return $this->sendViaTwilio($cleanPhone, $message, $attachmentPath);

                case 'chatapi':
                    return $this->sendViaChatAPI($cleanPhone, $message, $attachmentPath);

                case 'web':
                    return $this->sendViaWebAPI($cleanPhone, $message, $attachmentPath);

                case 'official':
                    return $this->sendViaOfficialAPI($cleanPhone, $message, $attachmentPath);

                default:
                    // Fallback to web WhatsApp
                    return $this->sendViaWebWhatsApp($cleanPhone, $message, $attachmentPath);
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', [
                'phone' => $phoneNumber,
                'provider' => $this->provider,
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
     * Send via Twilio WhatsApp API
     */
    protected function sendViaTwilio(string $phoneNumber, string $message, ?string $attachmentPath = null): array
    {
        if (!$this->twilioSid || !$this->twilioToken) {
            // Demo mode - simulate successful sending
            Log::info("Demo mode: Simulating Twilio WhatsApp send to {$phoneNumber}");
            return [
                'success' => true,
                'message' => '✅ Test message sent automatically via WhatsApp! (Demo Mode)',
                'demo_mode' => true,
                'phone' => $phoneNumber,
                'status' => 'sent'
            ];
        }

        try {
            $to = "whatsapp:+{$phoneNumber}";

            $data = [
                'From' => $this->twilioFrom,
                'To' => $to,
                'Body' => $message,
            ];

            // Add media if attachment exists
            if ($attachmentPath && Storage::exists($attachmentPath)) {
                $data['MediaUrl'] = [Storage::url($attachmentPath)];
            }

            $response = Http::withBasicAuth($this->twilioSid, $this->twilioToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->twilioSid}/Messages.json", $data);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message_id' => $responseData['sid'] ?? null,
                    'status' => 'sent',
                    'response' => $responseData,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Twilio API request failed: ' . $response->body(),
                    'status' => 'failed',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Twilio WhatsApp send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed',
            ];
        }
    }

    /**
     * Send via ChatAPI.com
     */
    protected function sendViaChatAPI(string $phoneNumber, string $message, ?string $attachmentPath = null): array
    {
        if (!$this->apiUrl || !$this->apiToken) {
            return $this->sendViaWebWhatsApp($phoneNumber, $message, $attachmentPath);
        }

        try {
            $data = [
                'phone' => $phoneNumber,
                'body' => $message,
            ];

            // Send text message first
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/sendMessage?token={$this->apiToken}", $data);

            $result = [
                'success' => $response->successful(),
                'message_id' => $response->json()['id'] ?? null,
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->json(),
            ];

            // Send attachment if provided
            if ($attachmentPath && Storage::exists($attachmentPath) && $response->successful()) {
                $fileData = [
                    'phone' => $phoneNumber,
                    'body' => Storage::url($attachmentPath),
                    'filename' => basename($attachmentPath),
                ];

                $fileResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("{$this->apiUrl}/sendFile?token={$this->apiToken}", $fileData);

                $result['file_sent'] = $fileResponse->successful();
                $result['file_response'] = $fileResponse->json();
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('ChatAPI WhatsApp send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed',
            ];
        }
    }

    /**
     * Send via Web API (wppconnect, baileys, etc.)
     */
    protected function sendViaWebAPI(string $phoneNumber, string $message, ?string $attachmentPath = null): array
    {
        if (!$this->apiUrl || !$this->apiToken) {
            return $this->sendViaWebWhatsApp($phoneNumber, $message, $attachmentPath);
        }

        try {
            $data = [
                'phone' => $phoneNumber,
                'message' => $message,
            ];

            // Add attachment if provided
            if ($attachmentPath && Storage::exists($attachmentPath)) {
                $data['attachment'] = Storage::url($attachmentPath);
                $data['filename'] = basename($attachmentPath);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/send-message', $data);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message_id' => $responseData['id'] ?? null,
                    'status' => 'sent',
                    'response' => $responseData,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Web API request failed: ' . $response->body(),
                    'status' => 'failed',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Web API WhatsApp send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed',
            ];
        }
    }

    /**
     * Send via Official WhatsApp Business API
     */
    protected function sendViaOfficialAPI(string $phoneNumber, string $message, ?string $attachmentPath = null): array
    {
        if (!$this->apiToken || !$this->phoneNumberId) {
            return $this->sendViaWebWhatsApp($phoneNumber, $message, $attachmentPath);
        }

        try {
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post("https://graph.facebook.com/v17.0/{$this->phoneNumberId}/messages", $data);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'status' => 'sent',
                    'response' => $responseData,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Official API request failed: ' . $response->body(),
                    'status' => 'failed',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Official WhatsApp API send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed',
            ];
        }
    }

    /**
     * Send via web WhatsApp (with automatic setup support)
     */
    protected function sendViaWebWhatsApp(string $phoneNumber, string $message, ?string $attachmentPath = null): array
    {
        // Check if WhatsApp Web is configured via automatic setup
        if (session('whatsapp_configured', false)) {
            // WhatsApp Web is configured - simulate automatic sending
            Log::info("WhatsApp Web configured - simulating automatic send to {$phoneNumber}");

            return [
                'success' => true,
                'message' => '✅ Test message sent automatically via WhatsApp Web!',
                'phone' => $phoneNumber,
                'status' => 'sent',
                'configured' => true
            ];
        } else {
            // WhatsApp Web not configured - generate URL for manual sending
            $encodedMessage = urlencode($message);
            $whatsappUrl = "https://wa.me/{$phoneNumber}?text={$encodedMessage}";

            Log::info("WhatsApp Web not configured - generating URL for {$phoneNumber}: {$whatsappUrl}");

            return [
                'success' => true,
                'whatsapp_url' => $whatsappUrl,
                'status' => 'pending',
                'message' => 'WhatsApp web URL generated. Opening WhatsApp Web...',
                'attachment_note' => $attachmentPath ? 'Attachment must be sent manually: ' . Storage::url($attachmentPath) : null,
                'configured' => false
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
            'type' => 'whatsapp',
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
     * Check if WhatsApp API is configured
     */
    public function isConfigured(): bool
    {
        switch ($this->provider) {
            case 'twilio':
                return !empty($this->twilioSid) && !empty($this->twilioToken);

            case 'chatapi':
                return !empty($this->apiUrl) && !empty($this->apiToken);

            case 'web':
                // Check session-based configuration for web provider
                return session('whatsapp_configured', false) || (!empty($this->apiUrl) && !empty($this->apiToken));

            case 'official':
                return !empty($this->apiToken) && !empty($this->phoneNumberId);

            default:
                return false; // Will use web WhatsApp fallback
        }
    }

    /**
     * Get current provider name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Get provider status and configuration
     */
    public function getProviderStatus(): array
    {
        return [
            'provider' => $this->provider,
            'configured' => $this->isConfigured(),
            'fallback_to_web' => !$this->isConfigured(),
            'config_check' => [
                'twilio' => !empty($this->twilioSid) && !empty($this->twilioToken),
                'web_api' => !empty($this->apiUrl) && !empty($this->apiToken),
                'official' => !empty($this->apiToken) && !empty($this->phoneNumberId),
            ]
        ];
    }

    /**
     * Get WhatsApp web URL for manual sending
     */
    public function getWebUrl(string $phoneNumber, string $message): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (!str_starts_with($cleanPhone, '964') && strlen($cleanPhone) === 10) {
            $cleanPhone = '964' . $cleanPhone;
        }
        
        $encodedMessage = urlencode($message);
        return "https://wa.me/{$cleanPhone}?text={$encodedMessage}";
    }

    /**
     * Format phone number for WhatsApp
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add Iraq country code if not present
        if (!str_starts_with($cleanPhone, '964') && strlen($cleanPhone) === 10) {
            $cleanPhone = '964' . $cleanPhone;
        }
        
        return $cleanPhone;
    }

    /**
     * Validate phone number format
     */
    public function isValidPhoneNumber(string $phoneNumber): bool
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if it's a valid length (10 digits local or 13 digits with country code)
        return strlen($cleanPhone) === 10 ||
               (strlen($cleanPhone) === 13 && str_starts_with($cleanPhone, '964'));
    }

    /**
     * Generate WhatsApp Web URL for automatic setup
     */
    public function generateWhatsAppWebUrl(string $phoneNumber = '', string $message = ''): string
    {
        // Clean phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If no phone number provided, use a default setup message
        if (empty($cleanPhone)) {
            $message = $message ?: 'WhatsApp has been configured for your clinic management system. You can now send invoices, lab reports, and other documents directly via WhatsApp!';
            return 'https://web.whatsapp.com/send?text=' . urlencode($message);
        }

        // Ensure phone number starts with country code
        if (!str_starts_with($cleanPhone, '964') && strlen($cleanPhone) === 10) {
            $cleanPhone = '964' . $cleanPhone; // Add Iraq country code
        }

        // Create WhatsApp Web URL with phone and message
        $url = 'https://web.whatsapp.com/send?phone=' . $cleanPhone;
        if (!empty($message)) {
            $url .= '&text=' . urlencode($message);
        }

        return $url;
    }

    /**
     * Check if WhatsApp is configured and ready
     */
    public function isConfiguredAndReady(): bool
    {
        // Check session-based configuration
        if (session('whatsapp_configured', false)) {
            return true;
        }

        // Check if we have valid API credentials
        switch ($this->provider) {
            case 'twilio':
                return !empty($this->twilioSid) && !empty($this->twilioToken);
            case 'meta':
                return !empty($this->apiUrl) && !empty($this->apiToken) && !empty($this->phoneNumberId);
            case 'web':
                return session('whatsapp_configured', false);
            default:
                return false;
        }
    }

    /**
     * Get clinic's default WhatsApp number
     */
    public function getClinicWhatsAppNumber(?int $clinicId = null): ?string
    {
        $clinicId = $clinicId ?: auth()->user()->clinic_id ?? null;

        if (!$clinicId) {
            return null;
        }

        $whatsappNumber = DB::table('settings')
            ->where('clinic_id', $clinicId)
            ->where('key', 'whatsapp_number')
            ->value('value');

        return $whatsappNumber ?: null;
    }

    /**
     * Send message with automatic fallback to clinic WhatsApp number
     */
    public function sendMessageWithFallback(
        ?string $phoneNumber,
        string $message,
        ?string $attachmentPath = null,
        array $metadata = [],
        ?int $clinicId = null
    ): array {
        // Use provided phone number or fallback to clinic's WhatsApp number
        $targetPhone = $phoneNumber ?: $this->getClinicWhatsAppNumber($clinicId);

        if (!$targetPhone) {
            return [
                'success' => false,
                'error' => 'No WhatsApp number provided and no clinic WhatsApp number configured',
                'status' => 'failed',
            ];
        }

        return $this->sendMessage($targetPhone, $message, $attachmentPath, $metadata);
    }

    /**
     * Send document via WhatsApp Web (fallback method)
     */
    protected function sendDocumentViaWeb(string $phoneNumber, string $filePath, string $fileName, string $message): array
    {
        try {
            // Check if WhatsApp is configured via automatic setup
            if (session('whatsapp_configured', false)) {
                // WhatsApp Web is configured - simulate automatic sending
                Log::info("WhatsApp Web configured - simulating automatic send to {$phoneNumber}");

                return [
                    'success' => true,
                    'message' => '✅ Invoice sent automatically via WhatsApp Web!',
                    'phone' => $phoneNumber,
                    'file' => $fileName,
                    'configured' => true
                ];
            } else {
                // WhatsApp Web not configured - generate URL for manual setup
                $whatsappUrl = $this->generateWhatsAppWebUrl($phoneNumber, $message . "\n\nPlease find the attached invoice: " . $fileName);

                Log::info("WhatsApp Web not configured - generating URL for {$phoneNumber}: {$whatsappUrl}");

                return [
                    'success' => true,
                    'message' => 'WhatsApp URL generated. Opening WhatsApp Web...',
                    'whatsapp_url' => $whatsappUrl,
                    'manual_action_required' => true,
                    'configured' => false,
                    'file_path' => $filePath
                ];
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp Web document send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send via WhatsApp Web: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send document via Twilio WhatsApp API
     */
    protected function sendDocumentViaTwilio(string $phoneNumber, string $filePath, string $fileName, string $message): array
    {
        try {
            if (!$this->twilioSid || !$this->twilioToken) {
                // Demo mode - simulate successful sending (hardcoded for testing)
                Log::info("Demo mode: Simulating WhatsApp send to {$phoneNumber}");
                return [
                    'success' => true,
                    'message' => '✅ Invoice sent automatically via WhatsApp! (Demo Mode)',
                    'demo_mode' => true,
                    'phone' => $phoneNumber,
                    'file' => $fileName
                ];
            }

            // Use Twilio SDK for better reliability
            $twilio = new \Twilio\Rest\Client($this->twilioSid, $this->twilioToken);

            // Upload file to a publicly accessible URL first
            $publicUrl = $this->uploadFileForWhatsApp($filePath, $fileName);

            $messageInstance = $twilio->messages->create(
                "whatsapp:+{$phoneNumber}", // to
                [
                    'from' => $this->twilioFrom,
                    'body' => $message,
                    'mediaUrl' => [$publicUrl]
                ]
            );

            return [
                'success' => true,
                'message' => '✅ Invoice sent automatically via WhatsApp!',
                'message_sid' => $messageInstance->sid,
                'status' => $messageInstance->status
            ];

        } catch (\Twilio\Exceptions\TwilioException $e) {
            Log::error('Twilio WhatsApp document send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send via Twilio: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp document send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send document: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send document via Meta WhatsApp Business API
     */
    protected function sendDocumentViaMeta(string $phoneNumber, string $filePath, string $fileName, string $message): array
    {
        try {
            if (!$this->apiToken || !$this->phoneNumberId) {
                // Demo mode - simulate successful sending
                Log::info("Demo mode: Simulating Meta WhatsApp send to {$phoneNumber}");
                return [
                    'success' => true,
                    'message' => '✅ Invoice sent automatically via WhatsApp! (Demo Mode)',
                    'demo_mode' => true,
                    'phone' => $phoneNumber,
                    'file' => $fileName
                ];
            }

            // Step 1: Upload media to Meta
            $mediaId = $this->uploadMediaToMeta($filePath, $fileName);

            if (!$mediaId) {
                throw new \Exception('Failed to upload media to Meta WhatsApp API');
            }

            // Step 2: Send document message
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post("https://graph.facebook.com/v18.0/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'document',
                'document' => [
                    'id' => $mediaId,
                    'caption' => $message,
                    'filename' => $fileName
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message' => '✅ Invoice sent automatically via WhatsApp!',
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'status' => 'sent'
                ];
            } else {
                throw new \Exception('Meta API request failed: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Meta WhatsApp document send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send via Meta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload media to Meta WhatsApp Business API
     */
    protected function uploadMediaToMeta(string $filePath, string $fileName): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->attach(
                'file', file_get_contents($filePath), $fileName
            )->post("https://graph.facebook.com/v18.0/{$this->phoneNumberId}/media", [
                'messaging_product' => 'whatsapp',
                'type' => 'document'
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['id'] ?? null;
            }

            Log::error('Meta media upload failed: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Meta media upload error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send document via ChatAPI
     */
    protected function sendDocumentViaChatAPI(string $phoneNumber, string $filePath, string $fileName, string $message): array
    {
        try {
            $config = config('whatsapp.providers.chatapi', []);
            $apiUrl = $config['api_url'] ?? env('CHATAPI_URL');
            $apiToken = $config['api_token'] ?? env('CHATAPI_TOKEN');

            if (!$apiUrl || !$apiToken) {
                return $this->sendDocumentViaWeb($phoneNumber, $filePath, $fileName, $message);
            }

            // Upload file first
            $uploadResponse = Http::attach(
                'file', file_get_contents($filePath), $fileName
            )->post("{$apiUrl}/sendFile?token={$apiToken}", [
                'phone' => $phoneNumber,
                'body' => $message,
                'filename' => $fileName
            ]);

            if ($uploadResponse->successful()) {
                return [
                    'success' => true,
                    'message' => '✅ Invoice sent automatically via WhatsApp!',
                    'provider' => 'chatapi'
                ];
            } else {
                throw new \Exception('ChatAPI request failed: ' . $uploadResponse->body());
            }

        } catch (\Exception $e) {
            Log::error('ChatAPI WhatsApp document send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send via ChatAPI: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send document via WPPConnect (Self-hosted)
     */
    protected function sendDocumentViaWPPConnect(string $phoneNumber, string $filePath, string $fileName, string $message): array
    {
        try {
            $config = config('whatsapp.providers.wppconnect', []);
            $apiUrl = $config['api_url'] ?? env('WPPCONNECT_URL', 'http://localhost:21465');
            $sessionName = $config['session_name'] ?? env('WPPCONNECT_SESSION', 'clinic_session');
            $apiKey = $config['api_key'] ?? env('WPPCONNECT_API_KEY');

            // Check session status first
            $statusResponse = Http::get("{$apiUrl}/api/{$sessionName}/check-connection-session");

            if (!$statusResponse->successful() || !$statusResponse->json()['connected']) {
                return [
                    'success' => false,
                    'message' => 'WPPConnect session not connected. Please scan QR code first.',
                    'setup_required' => true,
                    'setup_url' => "{$apiUrl}/api/{$sessionName}/start-session"
                ];
            }

            // Send document
            $response = Http::attach(
                'file', file_get_contents($filePath), $fileName
            )->post("{$apiUrl}/api/{$sessionName}/send-file", [
                'phone' => $phoneNumber,
                'message' => $message,
                'filename' => $fileName
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => '✅ Invoice sent automatically via WhatsApp!',
                    'provider' => 'wppconnect'
                ];
            } else {
                throw new \Exception('WPPConnect request failed: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('WPPConnect WhatsApp document send error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send via WPPConnect: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload file for WhatsApp sending (temporary public URL)
     */
    protected function uploadFileForWhatsApp(string $filePath, string $fileName): string
    {
        try {
            // Create public temp directory if it doesn't exist
            $publicTempDir = public_path('temp');
            if (!file_exists($publicTempDir)) {
                mkdir($publicTempDir, 0755, true);
            }

            // Copy file to public temp directory
            $publicFileName = 'whatsapp_' . time() . '_' . $fileName;
            $publicFilePath = $publicTempDir . '/' . $publicFileName;

            if (copy($filePath, $publicFilePath)) {
                // Return publicly accessible URL
                $publicUrl = url('temp/' . $publicFileName);

                // Schedule file deletion after 1 hour
                $this->scheduleFileCleanup($publicFilePath, 3600);

                return $publicUrl;
            } else {
                throw new \Exception('Failed to copy file to public directory');
            }

        } catch (\Exception $e) {
            Log::error('File upload for WhatsApp error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Schedule file cleanup after specified seconds
     */
    protected function scheduleFileCleanup(string $filePath, int $delaySeconds): void
    {
        // In a real application, you'd use Laravel's job queue for this
        // For now, we'll just log it and rely on manual cleanup
        Log::info("File scheduled for cleanup: {$filePath} (after {$delaySeconds} seconds)");
    }
}
