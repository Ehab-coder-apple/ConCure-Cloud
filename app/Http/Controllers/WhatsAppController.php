<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Patient;

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


        $status = $this->whatsappService->getProviderStatus();

        // Try to get server status if web provider is configured
        $serverStatus = null;
        if (($status['provider'] ?? null) === 'web' && ($status['configured'] ?? false)) {
            $apiUrl = env('WHATSAPP_API_URL');
            if (!empty($apiUrl)) {
                try {
                    $response = Http::timeout(5)->get(rtrim($apiUrl, '/') . '/status');
                    if ($response->successful()) {
                        $serverStatus = $response->json();
                    }
                } catch (\Exception $e) {
                    $serverStatus = ['error' => $e->getMessage()];
                }
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

    /**
     * Return patients (JSON) filtered by WhatsApp availability and status
     */
    public function patientsList(Request $request)
    {
        $user = auth()->user();
        $status = $request->input('status', 'active'); // active|inactive|all
        $type = $request->input('type', 'both'); // new|updated|both
        $since = $request->input('since');
        try {
            $sinceDate = $since ? \Carbon\Carbon::parse($since)->startOfDay() : now()->subDays(30)->startOfDay();
        } catch (\Throwable $e) {
            $sinceDate = now()->subDays(30)->startOfDay();
        }

        $query = Patient::query();
        // Restrict to clinic for clinic users; super admin (no clinic) sees all
        if (!empty($user->clinic_id)) {
            $query->where('clinic_id', $user->clinic_id);
        }
        $query->whereNotNull('whatsapp_phone')
              ->where('whatsapp_phone', '!=', '');

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        // date filters (registration/updates)
        if ($type === 'new') {
            $query->where('created_at', '>=', $sinceDate);
        } elseif ($type === 'updated') {
            $query->where('updated_at', '>=', $sinceDate);
        } else { // both
            $query->where(function($q) use ($sinceDate){
                $q->where('created_at', '>=', $sinceDate)
                  ->orWhere('updated_at', '>=', $sinceDate);
            });
        }

        $patients = $query->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(1000)
            ->get(['id','first_name','last_name','whatsapp_phone','is_active']);

        $out = $patients->map(function($p){
            $display = preg_replace('/\s+/', ' ', trim((string)$p->whatsapp_phone));
            return [
                'id' => $p->id,
                'name' => trim(($p->first_name.' '.$p->last_name)) ?: ('#'.$p->id),
                'phone' => $display,
                'is_active' => (bool)$p->is_active,
            ];
        })->values();

        return response()->json(['success' => true, 'patients' => $out]);
    }

    /**
     * Broadcast a WhatsApp message to selected patients
     */
    public function broadcast(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'patient_ids' => 'required|array|min:1',
            'patient_ids.*' => 'integer',
            'message' => 'required|string|max:2000',
        ]);

        $patientsQuery = Patient::query();
        if (!empty($user->clinic_id)) {
            $patientsQuery->where('clinic_id', $user->clinic_id);
        }
        $patients = $patientsQuery
            ->whereIn('id', $data['patient_ids'])
            ->whereNotNull('whatsapp_phone')
            ->where('whatsapp_phone','!=','')
            ->get();

        $results = [ 'sent' => [], 'pending' => [], 'failed' => [] ];

        foreach ($patients as $p) {
            $res = $this->whatsappService->sendMessage($p->whatsapp_phone, $data['message']);

            // Log each attempt
            try {
                $status = $res['success'] ? ($res['status'] ?? 'sent') : 'failed';
                $this->whatsappService->logCommunication(
                    $p->id,
                    $user->clinic_id,
                    $p->whatsapp_phone,
                    $data['message'],
                    null,
                    null,
                    $status,
                    $res['error'] ?? null,
                    $res['message_id'] ?? null,
                    $res,
                    $user->id
                );
            } catch (\Throwable $e) { /* ignore logging errors */ }

            if (!empty($res['success'])) {
                if (!empty($res['whatsapp_url'])) {
                    $results['pending'][] = [
                        'patient_id' => $p->id,
                        'name' => trim(($p->first_name.' '.$p->last_name)),
                        'phone' => $p->whatsapp_phone,
                        'url' => $res['whatsapp_url'],
                    ];
                } else {
                    $results['sent'][] = [ 'patient_id' => $p->id, 'name' => trim(($p->first_name.' '.$p->last_name)), 'phone' => $p->whatsapp_phone ];
                }
            } else {
                $results['failed'][] = [ 'patient_id' => $p->id, 'name' => trim(($p->first_name.' '.$p->last_name)), 'phone' => $p->whatsapp_phone, 'error' => $res['error'] ?? 'unknown' ];
            }
        }

        return response()->json(['success' => true] + $results);
    }

}
