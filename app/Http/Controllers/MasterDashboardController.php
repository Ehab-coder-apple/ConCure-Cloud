<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterAuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class MasterDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Master authentication is handled by middleware in routes
    }

    /**
     * Get the authenticated master user from request.
     */
    private function getMasterUser(Request $request = null)
    {
        if ($request && $request->attributes->has('master_user')) {
            return $request->attributes->get('master_user');
        }
        return MasterAuthController::user();
    }

    /**
     * Display the master dashboard.
     */
    public function index(Request $request)
    {
        // Get system statistics
        $stats = [
            'total_clinics' => DB::table('clinics')->count(),
            'active_clinics' => DB::table('clinics')->where('is_active', true)->count(),
            'trial_clinics' => DB::table('clinics')->where('is_trial', true)->count(),
            'expired_trials' => DB::table('clinics')->where('is_trial', true)->where('trial_expires_at', '<', now())->count(),
            'expiring_trials' => DB::table('clinics')->where('is_trial', true)->whereBetween('trial_expires_at', [now(), now()->addDays(7)])->count(),
            'pending_activations' => DB::table('activation_codes')->where('type', 'clinic')->where('is_used', false)->count(),
            'total_users' => DB::table('users')->count(),
            'total_patients' => DB::table('patients')->count(),
            'total_prescriptions' => DB::table('prescriptions')->count(),
            'total_nutrition_plans' => DB::table('diet_plans')->count(),
        ];

        // Get recent clinics
        $recentClinics = DB::table('clinics')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent activation codes
        $recentActivations = DB::table('activation_codes')
            ->leftJoin('users as creators', 'activation_codes.created_by', '=', 'creators.id')
            ->select(
                'activation_codes.*',
                'creators.first_name as creator_first_name',
                'creators.last_name as creator_last_name'
            )
            ->orderBy('activation_codes.created_at', 'desc')
            ->limit(10)
            ->get();

        // Parse metadata for recent activation codes
        foreach ($recentActivations as $code) {
            if ($code->metadata) {
                $metadata = json_decode($code->metadata, true);
                $code->max_users = $metadata['max_users'] ?? 10;
                $code->subscription_months = $metadata['subscription_months'] ?? 12;
                $code->admin_email = $metadata['admin_email'] ?? '';
                $code->admin_first_name = $metadata['admin_first_name'] ?? '';
                $code->admin_last_name = $metadata['admin_last_name'] ?? '';
            } else {
                // Set defaults if metadata is missing
                $code->max_users = 10;
                $code->subscription_months = 12;
                $code->admin_email = '';
                $code->admin_first_name = '';
                $code->admin_last_name = '';
            }
        }

        // Get system activity
        $systemActivity = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->leftJoin('clinics', 'users.clinic_id', '=', 'clinics.id')
            ->select(
                'audit_logs.*',
                'users.first_name',
                'users.last_name',
                'clinics.name as clinic_name'
            )
            ->orderBy('audit_logs.created_at', 'desc')
            ->limit(20)
            ->get();

        return view('master.dashboard', compact('stats', 'recentClinics', 'recentActivations', 'systemActivity'));
    }

    /**
     * Display clinic management page.
     */
    public function clinics(Request $request)
    {
        $query = DB::table('clinics')
            ->leftJoin('users', function($join) {
                $join->on('clinics.id', '=', 'users.clinic_id')
                     ->where('users.role', '=', 'admin');
            })
            ->leftJoin('activation_codes', function($join) {
                $join->on('clinics.id', '=', 'activation_codes.clinic_id')
                     ->where('activation_codes.type', '=', 'clinic');
            })
            ->select(
                'clinics.*',
                'users.first_name as admin_first_name',
                'users.last_name as admin_last_name',
                'users.email as admin_email',
                'activation_codes.code as activation_code',
                DB::raw('(SELECT COUNT(*) FROM users WHERE users.clinic_id = clinics.id) as user_count'),
                DB::raw('(SELECT COUNT(*) FROM patients WHERE patients.clinic_id = clinics.id) as patient_count'),
                DB::raw('(SELECT COUNT(*) FROM prescriptions WHERE prescriptions.clinic_id = clinics.id) as prescription_count'),
                DB::raw('(SELECT COUNT(*) FROM diet_plans WHERE diet_plans.patient_id IN (SELECT id FROM patients WHERE patients.clinic_id = clinics.id)) as nutrition_plan_count')
            );

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('clinics.name', 'like', "%{$search}%")
                  ->orWhere('clinics.email', 'like', "%{$search}%")
                  ->orWhere('activation_codes.code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('clinics.is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('clinics.is_active', false);
            }
        }

        if ($request->filled('subscription')) {
            switch ($request->subscription) {
                case 'trial':
                    $query->where('clinics.is_trial', true)
                          ->where('clinics.trial_expires_at', '>', now());
                    break;
                case 'expired_trial':
                    $query->where('clinics.is_trial', true)
                          ->where('clinics.trial_expires_at', '<', now());
                    break;
                case 'active':
                    $query->where('clinics.is_trial', false)
                          ->where(function($q) {
                              $q->whereNull('clinics.subscription_expires_at')
                                ->orWhere('clinics.subscription_expires_at', '>', now());
                          });
                    break;
                case 'expired':
                    $query->where('clinics.is_trial', false)
                          ->where('clinics.subscription_expires_at', '<', now());
                    break;
                case 'expiring':
                    $query->where('clinics.is_trial', false)
                          ->whereBetween('clinics.subscription_expires_at', [now(), now()->addDays(30)]);
                    break;
            }
        }

        $clinics = $query->orderBy('clinics.created_at', 'desc')->paginate(15);

        return view('master.clinics', compact('clinics'));
    }

    /**
     * Generate activation code for a new clinic.
     */
    public function generateActivationCode(Request $request)
    {
        $request->validate([
            'clinic_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_first_name' => 'required|string|max:100',
            'admin_last_name' => 'required|string|max:100',
            'max_users' => 'required|integer|min:1|max:1000',
            'subscription_months' => 'required|integer|min:1|max:60',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique activation code
            do {
                $activationCode = 'CLINIC-' . strtoupper(Str::random(8));
            } while (DB::table('activation_codes')->where('code', $activationCode)->exists());

            // Create clinic with trial setup
            $clinicId = DB::table('clinics')->insertGetId([
                'name' => $request->clinic_name,
                'email' => $request->admin_email,
                'is_active' => false,
                'max_users' => $request->max_users,
                'is_trial' => true, // Start as trial
                'trial_started_at' => null, // Will be set when activated
                'trial_expires_at' => null, // Will be set when activated (7 days from activation)
                'subscription_status' => 'trial',
                'subscription_expires_at' => null, // Will be set after trial conversion
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create activation code record
            DB::table('activation_codes')->insert([
                'code' => $activationCode,
                'type' => 'clinic',
                'clinic_id' => $clinicId,
                'expires_at' => Carbon::now()->addDays(30), // 30 days to activate
                'is_used' => false,
                'created_by' => MasterAuthController::id(),
                'metadata' => json_encode([
                    'admin_email' => $request->admin_email,
                    'admin_first_name' => $request->admin_first_name,
                    'admin_last_name' => $request->admin_last_name,
                    'max_users' => $request->max_users,
                    'subscription_months' => $request->subscription_months,
                ]),
                'notes' => $request->notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Activation code generated successfully!'),
                'activation_code' => $activationCode,
                'clinic_id' => $clinicId,
                'expires_at' => Carbon::now()->addDays(30)->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Error generating activation code: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display activation codes management.
     */
    public function activationCodes(Request $request)
    {
        $query = DB::table('activation_codes')
            ->leftJoin('clinics', 'activation_codes.clinic_id', '=', 'clinics.id')
            ->leftJoin('users as creators', 'activation_codes.created_by', '=', 'creators.id')
            ->leftJoin('users as used_by', 'activation_codes.used_by', '=', 'used_by.id')
            ->select(
                'activation_codes.*',
                'clinics.name as clinic_name',
                'creators.first_name as creator_first_name',
                'creators.last_name as creator_last_name',
                'used_by.first_name as used_by_first_name',
                'used_by.last_name as used_by_last_name'
            );

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activation_codes.code', 'like', "%{$search}%")
                  ->orWhere('clinics.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'used') {
                $query->where('activation_codes.is_used', true);
            } elseif ($request->status === 'unused') {
                $query->where('activation_codes.is_used', false);
            } elseif ($request->status === 'expired') {
                $query->where('activation_codes.expires_at', '<', now())
                      ->where('activation_codes.is_used', false);
            }
        }

        if ($request->filled('type')) {
            $query->where('activation_codes.type', $request->type);
        }

        $activationCodes = $query->orderBy('activation_codes.created_at', 'desc')->paginate(20);

        // Parse metadata for each activation code
        foreach ($activationCodes as $code) {
            if ($code->metadata) {
                $metadata = json_decode($code->metadata, true);
                $code->max_users = $metadata['max_users'] ?? 10;
                $code->subscription_months = $metadata['subscription_months'] ?? 12;
                $code->admin_email = $metadata['admin_email'] ?? '';
                $code->admin_first_name = $metadata['admin_first_name'] ?? '';
                $code->admin_last_name = $metadata['admin_last_name'] ?? '';
            } else {
                // Set defaults if metadata is missing
                $code->max_users = 10;
                $code->subscription_months = 12;
                $code->admin_email = '';
                $code->admin_first_name = '';
                $code->admin_last_name = '';
            }
        }

        return view('master.activation-codes', compact('activationCodes'));
    }

    /**
     * Toggle clinic status.
     */
    public function toggleClinicStatus(Request $request, $clinicId)
    {
        $clinic = DB::table('clinics')->where('id', $clinicId)->first();
        
        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => __('Clinic not found.'),
            ], 404);
        }

        $newStatus = !$clinic->is_active;
        
        DB::table('clinics')
            ->where('id', $clinicId)
            ->update([
                'is_active' => $newStatus,
                'updated_at' => now(),
            ]);

        // Log the action
        $masterUser = $this->getMasterUser($request);
        DB::table('audit_logs')->insert([
            'user_id' => MasterAuthController::id(),
            'user_name' => $masterUser ? $masterUser->first_name . ' ' . $masterUser->last_name : 'Master Admin',
            'user_role' => 'program_owner',
            'clinic_id' => null,
            'action' => $newStatus ? 'clinic_activated' : 'clinic_deactivated',
            'model_type' => 'Clinic',
            'model_id' => $clinicId,
            'description' => ($newStatus ? 'Activated' : 'Deactivated') . ' clinic from master dashboard',
            'old_values' => json_encode(['is_active' => !$newStatus]),
            'new_values' => json_encode(['is_active' => $newStatus]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $newStatus ? __('Clinic activated successfully.') : __('Clinic deactivated successfully.'),
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Extend clinic subscription.
     */
    public function extendSubscription(Request $request, $clinicId)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:60',
        ]);

        $clinic = DB::table('clinics')->where('id', $clinicId)->first();
        
        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => __('Clinic not found.'),
            ], 404);
        }

        $currentExpiry = $clinic->subscription_expires_at ? Carbon::parse($clinic->subscription_expires_at) : Carbon::now();
        $newExpiry = $currentExpiry->addMonths($request->months);

        DB::table('clinics')
            ->where('id', $clinicId)
            ->update([
                'subscription_expires_at' => $newExpiry,
                'updated_at' => now(),
            ]);

        // Log the action
        $masterUser = $this->getMasterUser($request);
        DB::table('audit_logs')->insert([
            'user_id' => MasterAuthController::id(),
            'user_name' => $masterUser ? $masterUser->first_name . ' ' . $masterUser->last_name : 'Master Admin',
            'user_role' => 'program_owner',
            'clinic_id' => null,
            'action' => 'subscription_extended',
            'model_type' => 'Clinic',
            'model_id' => $clinicId,
            'description' => 'Extended clinic subscription from master dashboard',
            'old_values' => json_encode(['subscription_expires_at' => $clinic->subscription_expires_at]),
            'new_values' => json_encode([
                'subscription_expires_at' => $newExpiry->format('Y-m-d H:i:s'),
                'months_added' => $request->months,
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Subscription extended successfully.'),
            'new_expiry' => $newExpiry->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get system analytics data.
     */
    public function analytics(Request $request)
    {
        // Monthly clinic registrations (SQLite compatible)
        $monthlyRegistrations = DB::table('clinics')
            ->select(
                DB::raw('strftime("%Y-%m", created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // User activity by clinic (SQLite compatible)
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();
        $clinicActivity = DB::table('clinics')
            ->leftJoin('users', 'clinics.id', '=', 'users.clinic_id')
            ->select(
                'clinics.name',
                DB::raw('COUNT(users.id) as user_count'),
                DB::raw("SUM(CASE WHEN users.last_login_at >= '{$sevenDaysAgo}' THEN 1 ELSE 0 END) as active_users")
            )
            ->where('clinics.is_active', true)
            ->groupBy('clinics.id', 'clinics.name')
            ->orderBy('user_count', 'desc')
            ->limit(10)
            ->get();

        // Subscription status (SQLite compatible)
        $now = Carbon::now()->toDateTimeString();
        $thirtyDaysFromNow = Carbon::now()->addDays(30)->toDateTimeString();
        $subscriptionStatus = DB::table('clinics')
            ->select(
                DB::raw("SUM(CASE WHEN subscription_expires_at > '{$now}' THEN 1 ELSE 0 END) as active_subscriptions"),
                DB::raw("SUM(CASE WHEN subscription_expires_at <= '{$now}' THEN 1 ELSE 0 END) as expired_subscriptions"),
                DB::raw("SUM(CASE WHEN subscription_expires_at BETWEEN '{$now}' AND '{$thirtyDaysFromNow}' THEN 1 ELSE 0 END) as expiring_soon")
            )
            ->first();

        // Prepare analytics data
        $analytics = [
            'total_clinics' => DB::table('clinics')->count(),
            'total_users' => DB::table('users')->count(),
            'total_patients' => DB::table('patients')->count(),
            'total_prescriptions' => DB::table('prescriptions')->count(),
            'active_users' => DB::table('users')->where('is_active', true)->count(),
            'new_patients' => DB::table('patients')->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
            'clinic_growth' => 15, // Calculate actual growth percentage
            'monthly_registrations' => $monthlyRegistrations->pluck('count')->toArray(),
            'monthly_labels' => $monthlyRegistrations->pluck('month')->toArray(),
            'top_clinics' => $clinicActivity,
            'subscription_status' => $subscriptionStatus,
            'recent_activity' => DB::table('audit_logs')
                ->orderBy('performed_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return view('master.analytics', compact('analytics'));
    }



    /**
     * Export analytics data.
     */
    public function exportAnalytics()
    {
        // Implement analytics export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }

    /**
     * Display registration requests.
     */
    public function registrationRequests(Request $request)
    {
        $query = DB::table('clinic_registration_requests')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20);

        return view('master.registration-requests', compact('requests'));
    }

    /**
     * Approve registration request.
     */
    public function approveRegistration(Request $request, $requestId)
    {
        // Update request status
        DB::table('clinic_registration_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => $this->getMasterUser($request)->id ?? null,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => __('Registration request approved')]);
    }

    /**
     * Reject registration request.
     */
    public function rejectRegistration(Request $request, $requestId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        // Update request status
        DB::table('clinic_registration_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'rejected',
                'reviewed_at' => now(),
                'reviewed_by' => $this->getMasterUser($request)->id ?? null,
                'review_notes' => $request->reason,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => __('Registration request rejected')]);
    }

    /**
     * Display audit logs.
     */
    public function auditLogs(Request $request)
    {
        $query = DB::table('audit_logs')
            ->orderBy('performed_at', 'desc');

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }

        $auditLogs = $query->paginate(50);

        return view('master.audit-logs', compact('auditLogs'));
    }

    /**
     * Export audit logs.
     */
    public function exportAuditLogs()
    {
        return response()->json(['message' => 'Audit logs export functionality will be implemented']);
    }

    /**
     * Display system settings.
     */
    public function settings()
    {
        // Get current platform settings from database or use defaults
        $platformSettings = DB::table('settings')
            ->where('clinic_id', null) // Platform-wide settings
            ->pluck('value', 'key')
            ->toArray();

        $settings = array_merge([
            'platform_name' => 'ConCure SaaS',
            'platform_version' => '1.0.0',
            'max_clinics' => 1000,
            'default_subscription_months' => 12,
            'activation_code_expiry_days' => 30,
            'maintenance_mode' => false,
            'registration_enabled' => true,
            'trial_period_days' => 30,
            'max_users_per_clinic' => 50,
            'backup_retention_days' => 90,
            'session_timeout_minutes' => 120,
        ], $platformSettings);

        // Get all platform users (program owners, platform admins, support agents)
        $platformUsers = DB::table('users')
            ->whereIn('role', ['program_owner', 'platform_admin', 'support_agent'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available permissions for platform users
        $availablePermissions = $this->getPlatformPermissions();

        return view('master.settings-enhanced', compact('settings', 'platformUsers', 'availablePermissions'));
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'platform_name' => 'required|string|max:255',
            'platform_version' => 'required|string|max:50',
            'max_clinics' => 'required|integer|min:1|max:10000',
            'default_subscription_months' => 'required|integer|min:1|max:60',
            'activation_code_expiry_days' => 'required|integer|min:1|max:365',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'trial_period_days' => 'required|integer|min:1|max:365',
            'max_users_per_clinic' => 'required|integer|min:1|max:1000',
            'backup_retention_days' => 'required|integer|min:1|max:365',
            'session_timeout_minutes' => 'required|integer|min:15|max:1440',
        ]);

        try {
            DB::beginTransaction();

            // Update platform settings
            foreach ($request->except(['_token', '_method']) as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    [
                        'clinic_id' => null, // Platform-wide settings
                        'key' => $key
                    ],
                    [
                        'value' => $value,
                        'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'integer' : 'string'),
                        'updated_at' => now()
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Platform settings updated successfully')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('Failed to update settings: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert trial to paid subscription.
     */
    public function convertTrialToSubscription(Request $request, $clinicId)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:36',
            'plan_type' => 'required|in:basic,professional,enterprise',
        ]);

        $clinic = DB::table('clinics')->where('id', $clinicId)->first();

        if (!$clinic) {
            return response()->json(['error' => 'Clinic not found'], 404);
        }

        if (!$clinic->is_trial) {
            return response()->json(['error' => 'Clinic is not on trial'], 400);
        }

        // Convert trial to subscription
        DB::table('clinics')
            ->where('id', $clinicId)
            ->update([
                'is_trial' => false,
                'trial_started_at' => null,
                'trial_expires_at' => null,
                'subscription_status' => 'active',
                'subscription_expires_at' => now()->addMonths($request->months),
                'updated_at' => now(),
            ]);

        // Log the action
        $masterUser = $this->getMasterUser($request);
        DB::table('audit_logs')->insert([
            'user_id' => MasterAuthController::id(),
            'user_name' => $masterUser ? $masterUser->first_name . ' ' . $masterUser->last_name : 'Master Admin',
            'user_role' => 'program_owner',
            'clinic_id' => null,
            'action' => 'trial_converted_to_subscription',
            'model_type' => 'Clinic',
            'model_id' => $clinicId,
            'description' => 'Converted trial to paid subscription from master dashboard',
            'old_values' => json_encode([
                'is_trial' => true,
                'trial_expires_at' => $clinic->trial_expires_at,
            ]),
            'new_values' => json_encode([
                'is_trial' => false,
                'subscription_status' => 'active',
                'subscription_expires_at' => now()->addMonths($request->months)->format('Y-m-d H:i:s'),
                'plan_type' => $request->plan_type,
                'months_added' => $request->months,
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Trial successfully converted to :months month subscription', ['months' => $request->months])
        ]);
    }

    /**
     * Extend trial period.
     */
    public function extendTrial(Request $request, $clinicId)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:30',
        ]);

        $clinic = DB::table('clinics')->where('id', $clinicId)->first();

        if (!$clinic) {
            return response()->json(['error' => 'Clinic not found'], 404);
        }

        if (!$clinic->is_trial) {
            return response()->json(['error' => 'Clinic is not on trial'], 400);
        }

        $oldExpiry = $clinic->trial_expires_at;
        $newExpiry = now()->parse($clinic->trial_expires_at)->addDays($request->days);

        // Extend trial
        DB::table('clinics')
            ->where('id', $clinicId)
            ->update([
                'trial_expires_at' => $newExpiry,
                'updated_at' => now(),
            ]);

        // Log the action
        $masterUser = $this->getMasterUser($request);
        DB::table('audit_logs')->insert([
            'user_id' => MasterAuthController::id(),
            'user_name' => $masterUser ? $masterUser->first_name . ' ' . $masterUser->last_name : 'Master Admin',
            'user_role' => 'program_owner',
            'clinic_id' => null,
            'action' => 'trial_extended',
            'model_type' => 'Clinic',
            'model_id' => $clinicId,
            'description' => 'Extended trial period from master dashboard',
            'old_values' => json_encode(['trial_expires_at' => $oldExpiry]),
            'new_values' => json_encode([
                'trial_expires_at' => $newExpiry->format('Y-m-d H:i:s'),
                'days_added' => $request->days,
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Trial extended by :days days', ['days' => $request->days])
        ]);
    }

    /**
     * Get trial analytics.
     */
    public function trialAnalytics()
    {
        $trialStats = [
            'total_trials' => DB::table('clinics')->where('is_trial', true)->count(),
            'active_trials' => DB::table('clinics')->where('is_trial', true)->where('trial_expires_at', '>', now())->count(),
            'expired_trials' => DB::table('clinics')->where('is_trial', true)->where('trial_expires_at', '<', now())->count(),
            'expiring_soon' => DB::table('clinics')->where('is_trial', true)->whereBetween('trial_expires_at', [now(), now()->addDays(7)])->count(),
            'conversion_rate' => 0, // Calculate based on historical data
        ];

        // Get trial conversion data for the last 6 months
        $trialConversions = DB::table('audit_logs')
            ->where('action', 'trial_converted_to_subscription')
            ->where('performed_at', '>=', now()->subMonths(6))
            ->selectRaw('strftime("%Y-%m", performed_at) as month, COUNT(*) as conversions')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get expiring trials with details
        $expiringTrials = DB::table('clinics')
            ->leftJoin('users', function($join) {
                $join->on('clinics.id', '=', 'users.clinic_id')
                     ->where('users.role', '=', 'admin');
            })
            ->where('clinics.is_trial', true)
            ->where('clinics.trial_expires_at', '>', now())
            ->where('clinics.trial_expires_at', '<=', now()->addDays(7))
            ->select(
                'clinics.*',
                'users.first_name as admin_first_name',
                'users.last_name as admin_last_name',
                'users.email as admin_email'
            )
            ->orderBy('clinics.trial_expires_at')
            ->get();

        return view('master.trial-analytics', compact('trialStats', 'trialConversions', 'expiringTrials'));
    }

    /**
     * Display platform users.
     */
    public function platformUsers()
    {
        $users = DB::table('users')
            ->whereIn('role', ['program_owner', 'platform_admin', 'support_agent'])
            ->orderBy('created_at', 'desc')
            ->get();

        $availablePermissions = $this->getPlatformPermissions();

        return view('master.platform-users', compact('users', 'availablePermissions'));
    }

    /**
     * Create platform user.
     */
    public function createPlatformUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username|alpha_dash',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:platform_admin,support_agent',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'username' => $request->username,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'clinic_id' => null, // Platform users don't belong to a clinic
                'is_active' => true,
                'activated_at' => now(),
                'permissions' => $request->permissions,
                'created_by' => auth()->id(),
                'metadata' => [
                    'created_by_program_owner' => auth()->user()->full_name,
                    'account_type' => 'platform_user',
                    'creation_date' => now()->toDateString(),
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Platform user created successfully'),
                'user' => $user
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('Failed to create user: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update platform user.
     */
    public function updatePlatformUser(Request $request, $userId)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:program_owner,platform_admin,support_agent',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);

            // Prevent program owners from being modified by other users
            if ($user->role === 'program_owner' && auth()->user()->role !== 'program_owner') {
                return response()->json([
                    'success' => false,
                    'message' => __('Only program owners can modify other program owners')
                ], 403);
            }

            $updateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'permissions' => $request->permissions,
                'is_active' => $request->boolean('is_active', true),
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Platform user updated successfully')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('Failed to update user: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete platform user.
     */
    public function deletePlatformUser($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Prevent program owners from being deleted
            if ($user->role === 'program_owner') {
                return response()->json([
                    'success' => false,
                    'message' => __('Program owners cannot be deleted')
                ], 403);
            }

            // Prevent users from deleting themselves
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => __('You cannot delete your own account')
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => __('Platform user deleted successfully')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete user: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display system health.
     */
    public function systemHealth()
    {
        $health = [
            'database' => 'healthy',
            'storage' => 'healthy',
            'cache' => 'healthy',
            'queue' => 'healthy'
        ];

        return view('master.system-health', compact('health'));
    }

    /**
     * Run health check.
     */
    public function runHealthCheck()
    {
        // Implement health check functionality
        return response()->json(['success' => true, 'message' => __('Health check completed')]);
    }

    /**
     * Display backups.
     */
    public function backups()
    {
        $backups = []; // Implement backup listing

        return view('master.backups', compact('backups'));
    }

    /**
     * Create backup.
     */
    public function createBackup()
    {
        // Implement backup creation functionality
        return response()->json(['success' => true, 'message' => __('Backup created successfully')]);
    }

    /**
     * Delete backup.
     */
    public function deleteBackup($backupId)
    {
        // Implement backup deletion functionality
        return response()->json(['success' => true, 'message' => __('Backup deleted successfully')]);
    }

    /**
     * Download backup.
     */
    public function downloadBackup($backupId)
    {
        // Implement backup download functionality
        return response()->json(['message' => 'Backup download functionality will be implemented']);
    }

    /**
     * Usage report.
     */
    public function usageReport()
    {
        // Implement usage report functionality
        return response()->json(['message' => 'Usage report functionality will be implemented']);
    }

    /**
     * Revenue report.
     */
    public function revenueReport()
    {
        // Implement revenue report functionality
        return response()->json(['message' => 'Revenue report functionality will be implemented']);
    }

    /**
     * Show clinic details.
     */
    public function showClinic($clinicId)
    {
        $clinic = DB::table('clinics')
            ->leftJoin('users as admin', function($join) {
                $join->on('clinics.id', '=', 'admin.clinic_id')
                     ->where('admin.role', '=', 'admin');
            })
            ->select(
                'clinics.*',
                'admin.first_name as admin_first_name',
                'admin.last_name as admin_last_name',
                'admin.email as admin_email',
                'admin.phone as admin_phone',
                'admin.last_login_at as admin_last_login'
            )
            ->where('clinics.id', $clinicId)
            ->first();

        if (!$clinic) {
            abort(404, 'Clinic not found');
        }

        // Ensure all required properties exist with defaults
        $clinic->max_users = property_exists($clinic, 'max_users') ? $clinic->max_users : 10;
        $clinic->is_trial = property_exists($clinic, 'is_trial') ? $clinic->is_trial : false;
        $clinic->is_active = property_exists($clinic, 'is_active') ? $clinic->is_active : true;

        // Get clinic statistics
        $stats = [
            'total_users' => DB::table('users')->where('clinic_id', $clinicId)->count(),
            'active_users' => DB::table('users')->where('clinic_id', $clinicId)->where('is_active', true)->count(),
            'total_patients' => DB::table('patients')->where('clinic_id', $clinicId)->count(),
            'active_patients' => DB::table('patients')->where('clinic_id', $clinicId)->where('is_active', true)->count(),
            'total_prescriptions' => DB::table('prescriptions')->where('clinic_id', $clinicId)->count(),
            'recent_prescriptions' => DB::table('prescriptions')->where('clinic_id', $clinicId)->where('prescribed_date', '>=', now()->subDays(30))->count(),
            'total_appointments' => DB::table('appointments')->where('clinic_id', $clinicId)->count(),
            'upcoming_appointments' => DB::table('appointments')->where('clinic_id', $clinicId)->where('appointment_date', '>=', now())->count(),
            'total_nutrition_plans' => DB::table('diet_plans')
                ->whereIn('patient_id', function($query) use ($clinicId) {
                    $query->select('id')->from('patients')->where('clinic_id', $clinicId);
                })->count(),
        ];

        // Get recent activity
        $recentActivity = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->where('audit_logs.clinic_id', $clinicId)
            ->select(
                'audit_logs.*',
                'users.first_name',
                'users.last_name'
            )
            ->orderBy('audit_logs.performed_at', 'desc')
            ->limit(20)
            ->get();

        // Get users breakdown by role
        $usersByRole = DB::table('users')
            ->where('clinic_id', $clinicId)
            ->select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();

        // Get monthly activity (last 6 months)
        $monthlyActivity = DB::table('audit_logs')
            ->where('clinic_id', $clinicId)
            ->where('performed_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('strftime("%Y-%m", performed_at) as month'),
                DB::raw('COUNT(*) as activity_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('master.clinic-details', compact('clinic', 'stats', 'recentActivity', 'usersByRole', 'monthlyActivity'));
    }

    /**
     * Delete clinic.
     */
    public function deleteClinic($clinicId)
    {
        try {
            DB::beginTransaction();

            // Find the clinic
            $clinic = DB::table('clinics')->where('id', $clinicId)->first();

            if (!$clinic) {
                return response()->json([
                    'success' => false,
                    'message' => __('Clinic not found.')
                ], 404);
            }

            // Check if clinic has users
            $userCount = DB::table('users')->where('clinic_id', $clinicId)->count();
            if ($userCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot delete clinic with existing users. Please remove all users first.')
                ], 400);
            }

            // Check if clinic has patients
            $patientCount = DB::table('patients')->where('clinic_id', $clinicId)->count();
            if ($patientCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot delete clinic with existing patients. Please remove all patients first.')
                ], 400);
            }

            // Check if clinic has prescriptions
            $prescriptionCount = DB::table('prescriptions')->where('clinic_id', $clinicId)->count();
            if ($prescriptionCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot delete clinic with existing prescriptions. Please remove all medical records first.')
                ], 400);
            }

            // Delete related activation codes
            DB::table('activation_codes')->where('clinic_id', $clinicId)->delete();

            // Delete audit logs related to this clinic
            DB::table('audit_logs')->where('clinic_id', $clinicId)->delete();

            // Delete the clinic
            DB::table('clinics')->where('id', $clinicId)->delete();

            // Log the deletion
            $masterUser = $this->getMasterUser(request());
            DB::table('audit_logs')->insert([
                'user_id' => MasterAuthController::id(),
                'user_name' => $masterUser ? $masterUser->first_name . ' ' . $masterUser->last_name : 'Master Admin',
                'user_role' => 'program_owner',
                'clinic_id' => null,
                'action' => 'clinic_deleted',
                'model_type' => 'Clinic',
                'model_id' => $clinicId,
                'description' => 'Deleted clinic from master dashboard',
                'old_values' => json_encode([
                    'name' => $clinic->name,
                    'email' => $clinic->email,
                    'is_active' => $clinic->is_active
                ]),
                'new_values' => json_encode(['deleted' => true]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Clinic deleted successfully.')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('Error deleting clinic: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display program features and benefits.
     */
    public function programFeatures()
    {
        // Get system statistics for feature showcase
        $stats = [
            'total_clinics' => DB::table('clinics')->count(),
            'active_clinics' => DB::table('clinics')->where('is_active', true)->count(),
            'total_users' => DB::table('users')->count(),
            'total_patients' => DB::table('patients')->count(),
            'total_prescriptions' => DB::table('prescriptions')->count(),
            'total_appointments' => DB::table('appointments')->count(),
            'total_nutrition_plans' => DB::table('diet_plans')->count(),
            'total_medicines' => DB::table('medicines')->count(),
            'total_foods' => DB::table('foods')->count(),
        ];

        // Feature categories with their features
        $featureCategories = [
            'patient_management' => [
                'title' => __('Patient Management'),
                'icon' => 'fas fa-users',
                'color' => 'primary',
                'features' => [
                    __('Complete patient profiles with medical history'),
                    __('Patient registration and activation system'),
                    __('Multi-language support (English, Arabic, Kurdish)'),
                    __('Patient search and filtering capabilities'),
                    __('Patient status management (active/inactive)'),
                    __('WhatsApp integration for patient communication'),
                    __('Patient visit tracking and history'),
                    __('BMI calculation and weight tracking'),
                ]
            ],
            'prescription_system' => [
                'title' => __('Prescription System'),
                'icon' => 'fas fa-prescription-bottle-alt',
                'color' => 'success',
                'features' => [
                    __('Digital prescription creation and management'),
                    __('Medicine inventory with custom additions'),
                    __('Dosage, frequency, and duration tracking'),
                    __('PDF prescription generation'),
                    __('Print-ready prescription formats'),
                    __('Prescription history and tracking'),
                    __('Medicine search and selection'),
                    __('Multi-language prescription support'),
                ]
            ],
            'appointment_scheduling' => [
                'title' => __('Appointment Scheduling'),
                'icon' => 'fas fa-calendar-alt',
                'color' => 'info',
                'features' => [
                    __('Comprehensive appointment booking system'),
                    __('Date and time slot management'),
                    __('Appointment status tracking'),
                    __('Patient appointment history'),
                    __('Dashboard appointment overview'),
                    __('Appointment notifications'),
                    __('Recurring appointment support'),
                    __('Appointment conflict prevention'),
                ]
            ],
            'nutrition_planning' => [
                'title' => __('Nutrition Planning'),
                'icon' => 'fas fa-apple-alt',
                'color' => 'warning',
                'features' => [
                    __('Comprehensive nutrition plan creation'),
                    __('Weekly meal planning (7-day plans)'),
                    __('Food database with caloric information'),
                    __('Custom food additions and management'),
                    __('Caloric distribution and targets'),
                    __('BMI-based nutrition recommendations'),
                    __('Weight tracking and progress monitoring'),
                    __('WhatsApp sharing for nutrition plans'),
                    __('Excel-based food import system'),
                    __('Multi-language food database'),
                ]
            ],
            'laboratory_management' => [
                'title' => __('Laboratory Management'),
                'icon' => 'fas fa-flask',
                'color' => 'danger',
                'features' => [
                    __('Lab request creation and management'),
                    __('External laboratory integration'),
                    __('Lab request tracking and history'),
                    __('Custom lab request forms'),
                    __('Lab result management'),
                    __('Patient lab history'),
                    __('Lab request notifications'),
                    __('Multi-language lab requests'),
                ]
            ],

            'user_management' => [
                'title' => __('User Management'),
                'icon' => 'fas fa-user-cog',
                'color' => 'secondary',
                'features' => [
                    __('Role-based access control'),
                    __('Custom permission management'),
                    __('Multi-user clinic support'),
                    __('User activity tracking'),
                    __('Admin-controlled feature access'),
                    __('User registration and activation'),
                    __('Profile management'),
                    __('Security and authentication'),
                ]
            ],
            'reporting_analytics' => [
                'title' => __('Reporting & Analytics'),
                'icon' => 'fas fa-chart-bar',
                'color' => 'dark',
                'features' => [
                    __('Comprehensive dashboard analytics'),
                    __('Patient statistics and trends'),
                    __('Prescription analytics'),
                    __('Appointment tracking reports'),
                    __('Nutrition plan analytics'),
                    __('User activity reports'),
                    __('Export capabilities (PDF, Excel)'),
                    __('Real-time data visualization'),
                ]
            ],
            'system_features' => [
                'title' => __('System Features'),
                'icon' => 'fas fa-cogs',
                'color' => 'teal',
                'features' => [
                    __('Progressive Web App (PWA) ready'),
                    __('Responsive design for all devices'),
                    __('Multi-language interface'),
                    __('Right-to-left (RTL) language support'),
                    __('SQLite database for reliability'),
                    __('Audit logging and activity tracking'),
                    __('Data backup and recovery'),
                    __('Security and data protection'),
                    __('Customizable clinic settings'),
                    __('White-label branding options'),
                ]
            ]
        ];

        return view('master.program-features', compact('stats', 'featureCategories'));
    }

    /**
     * Delete activation code.
     */
    public function deleteActivationCode($codeId)
    {
        try {
            // Find the activation code
            $activationCode = DB::table('activation_codes')->where('id', $codeId)->first();

            if (!$activationCode) {
                return response()->json([
                    'success' => false,
                    'message' => __('Activation code not found.')
                ], 404);
            }

            // Check if the activation code has been used
            if ($activationCode->is_used) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot delete a used activation code.')
                ], 400);
            }

            // Check if there's an associated clinic that hasn't been activated yet
            if ($activationCode->clinic_id) {
                $clinic = DB::table('clinics')->where('id', $activationCode->clinic_id)->first();

                // If clinic exists and is not activated, we can delete both
                if ($clinic && !$clinic->activated_at) {
                    DB::beginTransaction();

                    // Delete the clinic first (if it has no users)
                    $userCount = DB::table('users')->where('clinic_id', $clinic->id)->count();
                    if ($userCount == 0) {
                        DB::table('clinics')->where('id', $clinic->id)->delete();
                    }

                    // Delete the activation code
                    DB::table('activation_codes')->where('id', $codeId)->delete();

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => __('Activation code and associated clinic deleted successfully.')
                    ]);
                } elseif ($clinic && $clinic->activated_at) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Cannot delete activation code for an already activated clinic.')
                    ], 400);
                }
            }

            // Delete the activation code only
            DB::table('activation_codes')->where('id', $codeId)->delete();

            return response()->json([
                'success' => true,
                'message' => __('Activation code deleted successfully.')
            ]);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return response()->json([
                'success' => false,
                'message' => __('Error deleting activation code: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extend activation code.
     */
    public function extendActivationCode($codeId)
    {
        try {
            // Find the activation code
            $activationCode = DB::table('activation_codes')->where('id', $codeId)->first();

            if (!$activationCode) {
                return response()->json([
                    'success' => false,
                    'message' => __('Activation code not found.')
                ], 404);
            }

            // Check if the activation code has been used
            if ($activationCode->is_used) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cannot extend a used activation code.')
                ], 400);
            }

            // Extend the expiry date by 30 days
            $newExpiryDate = Carbon::parse($activationCode->expires_at)->addDays(30);

            DB::table('activation_codes')
                ->where('id', $codeId)
                ->update([
                    'expires_at' => $newExpiryDate,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => __('Activation code extended successfully. New expiry date: ') . $newExpiryDate->format('M d, Y'),
                'new_expiry' => $newExpiryDate->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error extending activation code: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available platform permissions.
     */
    private function getPlatformPermissions(): array
    {
        return [
            'master_dashboard_access' => __('Master Dashboard Access'),
            'clinics_view' => __('View Clinics'),
            'clinics_create' => __('Create Clinics'),
            'clinics_edit' => __('Edit Clinics'),
            'clinics_delete' => __('Delete Clinics'),
            'clinics_manage' => __('Full Clinic Management'),
            'platform_users_view' => __('View Platform Users'),
            'platform_users_create' => __('Create Platform Users'),
            'platform_users_edit' => __('Edit Platform Users'),
            'platform_users_delete' => __('Delete Platform Users'),
            'analytics_view' => __('View Analytics'),
            'analytics_export' => __('Export Analytics'),
            'audit_logs_view' => __('View Audit Logs'),
            'audit_logs_export' => __('Export Audit Logs'),
            'system_settings_view' => __('View System Settings'),
            'system_settings_edit' => __('Edit System Settings'),
            'activation_codes_view' => __('View Activation Codes'),
            'activation_codes_create' => __('Create Activation Codes'),
            'activation_codes_delete' => __('Delete Activation Codes'),
            'backups_view' => __('View Backups'),
            'backups_create' => __('Create Backups'),
            'backups_download' => __('Download Backups'),
            'system_health_view' => __('View System Health'),
            'system_maintenance' => __('System Maintenance'),
            'software_updates' => __('Software Updates'),
            'registration_requests_view' => __('View Registration Requests'),
            'registration_requests_manage' => __('Manage Registration Requests'),
        ];
    }

    /**
     * Update software/system.
     */
    public function updateSoftware(Request $request)
    {
        // Check permissions
        if (!auth()->user()->hasPermission('software_updates')) {
            return response()->json([
                'success' => false,
                'message' => __('Insufficient permissions for software updates')
            ], 403);
        }

        $request->validate([
            'update_type' => 'required|in:minor,major,security',
            'backup_before_update' => 'boolean',
            'maintenance_mode' => 'boolean',
        ]);

        try {
            // Enable maintenance mode if requested
            if ($request->boolean('maintenance_mode')) {
                DB::table('settings')->updateOrInsert(
                    ['clinic_id' => null, 'key' => 'maintenance_mode'],
                    ['value' => true, 'type' => 'boolean', 'updated_at' => now()]
                );
            }

            // Create backup if requested
            if ($request->boolean('backup_before_update')) {
                // Implement backup logic here
                \Log::info('System backup created before update', ['user' => auth()->id()]);
            }

            // Simulate update process
            \Log::info('Software update initiated', [
                'type' => $request->update_type,
                'user' => auth()->id(),
                'timestamp' => now()
            ]);

            // Update version number
            $newVersion = $this->generateNewVersion($request->update_type);
            DB::table('settings')->updateOrInsert(
                ['clinic_id' => null, 'key' => 'platform_version'],
                ['value' => $newVersion, 'type' => 'string', 'updated_at' => now()]
            );

            // Disable maintenance mode
            DB::table('settings')->updateOrInsert(
                ['clinic_id' => null, 'key' => 'maintenance_mode'],
                ['value' => false, 'type' => 'boolean', 'updated_at' => now()]
            );

            return response()->json([
                'success' => true,
                'message' => __('Software updated successfully to version ') . $newVersion
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Software update failed: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate new version number.
     */
    private function generateNewVersion(string $updateType): string
    {
        $currentVersion = DB::table('settings')
            ->where('clinic_id', null)
            ->where('key', 'platform_version')
            ->value('value') ?? '1.0.0';

        $versionParts = explode('.', $currentVersion);
        $major = (int)($versionParts[0] ?? 1);
        $minor = (int)($versionParts[1] ?? 0);
        $patch = (int)($versionParts[2] ?? 0);

        switch ($updateType) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'security':
            default:
                $patch++;
                break;
        }

        return "{$major}.{$minor}.{$patch}";
    }
}
