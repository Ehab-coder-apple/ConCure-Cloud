<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Show WhatsApp configuration and status
     */
    public function index()
    {
        $user = auth()->user();

        // Only admins can access WhatsApp settings
        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can access WhatsApp settings.');
        }

        $status = $this->whatsappService->getProviderStatus();
        
        // Try to get server status if web provider is configured
        $serverStatus = null;
        if ($status['provider'] === 'web' && $status['configured']) {
            try {
                $response = Http::timeout(5)->get(env('WHATSAPP_API_URL') . '/status');
                if ($response->successful()) {
                    $serverStatus = $response->json();
                }
            } catch (\Exception $e) {
                $serverStatus = ['error' => $e->getMessage()];
            }
        }

        // Get clinic's WhatsApp number for pre-filling test form
        $clinicWhatsApp = $this->whatsappService->getClinicWhatsAppNumber();

        return view('whatsapp.index', compact('status', 'serverStatus', 'clinicWhatsApp'));
    }

    /**
     * Test WhatsApp message sending
     */
    public function test(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can test WhatsApp.');
        }

        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        $result = $this->whatsappService->sendMessage(
            $request->phone,
            $request->message
        );

        if ($result['success']) {
            if (isset($result['whatsapp_url'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Opening WhatsApp Web to send message...',
                    'whatsapp_url' => $result['whatsapp_url'],
                    'auto_open' => true,
                ]);
            } else if (isset($result['demo_mode']) && $result['demo_mode']) {
                // In demo mode, also provide WhatsApp Web URL for actual sending
                $whatsappUrl = $this->whatsappService->generateWhatsAppWebUrl(
                    $request->phone,
                    $request->message
                );
                return response()->json([
                    'success' => true,
                    'message' => 'Demo mode: Opening WhatsApp Web to send real message...',
                    'whatsapp_url' => $whatsappUrl,
                    'auto_open' => true,
                    'demo_mode' => true,
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Test message sent successfully!',
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Failed to send test message',
            ], 400);
        }
    }

    /**
     * Setup WhatsApp Web connection automatically
     */
    public function setupWhatsAppWeb(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can setup WhatsApp.');
        }

        try {
            // Generate WhatsApp Web URL for automatic setup
            $phoneNumber = $request->input('phone_number', '');
            $message = $request->input('message', 'Setting up WhatsApp for ' . ($user->clinic->name ?? 'clinic'));

            // Create WhatsApp Web URL
            $whatsappUrl = $this->whatsappService->generateWhatsAppWebUrl($phoneNumber, $message);

            // Store setup status in session
            session(['whatsapp_setup_initiated' => true]);
            session(['whatsapp_setup_time' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp URL generated. Opening WhatsApp Web...',
                'whatsapp_url' => $whatsappUrl,
                'auto_open' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to setup WhatsApp: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check WhatsApp setup status
     */
    public function checkSetupStatus()
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can check WhatsApp status.');
        }

        $setupInitiated = session('whatsapp_setup_initiated', false);
        $setupTime = session('whatsapp_setup_time');

        // Consider setup complete if initiated more than 30 seconds ago
        $setupComplete = $setupInitiated && $setupTime && now()->diffInSeconds($setupTime) > 30;

        if ($setupComplete) {
            // Mark WhatsApp as configured
            session(['whatsapp_configured' => true]);
        }

        return response()->json([
            'setup_initiated' => $setupInitiated,
            'setup_complete' => $setupComplete,
            'configured' => session('whatsapp_configured', false),
            'time_elapsed' => $setupTime ? now()->diffInSeconds($setupTime) : 0
        ]);
    }

    /**
     * Get WhatsApp server QR code (for web provider)
     */
    public function qrCode()
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can access WhatsApp QR code.');
        }

        $apiUrl = env('WHATSAPP_API_URL');
        if (!$apiUrl) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp API URL not configured',
            ], 400);
        }

        try {
            $response = Http::timeout(10)->get($apiUrl . '/qr');

            if ($response->successful()) {
                return response($response->body())
                    ->header('Content-Type', 'text/html');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get QR code from server',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp server is not running: ' . $e->getMessage(),
            ], 500);
        }
    }
}
