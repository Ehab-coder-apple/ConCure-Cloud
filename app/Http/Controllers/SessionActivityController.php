<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SessionActivityController extends Controller
{
    /**
     * Keep the session alive by updating last activity time.
     * Called periodically by JavaScript when user is active.
     */
    public function keepAlive(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'unauthenticated',
                'message' => 'User not authenticated'
            ], 401);
        }

        $user = Auth::user();

        // Update session last activity timestamp
        $timestamp = now()->timestamp;
        Session::put('last_activity', $timestamp);

        // Save the session to persist the changes
        $request->session()->save();
        $this->closeSessionLock($request);

        return response()->json([
            'status' => 'alive',
            'message' => 'Session kept alive',
            'timestamp' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->name,
            ]
        ]);
    }

    /**
     * Check session status and return remaining time.
     */
    public function checkStatus(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Session has expired'
            ], 401);
        }

        $lastActivity = Session::get('last_activity', now()->timestamp);
        $timeoutMinutes = config('concure.auto_logout.timeout_minutes', 10);
        $timeoutSeconds = $timeoutMinutes * 60;
        
        $elapsedSeconds = now()->timestamp - $lastActivity;
        $remainingSeconds = max(0, $timeoutSeconds - $elapsedSeconds);

        $this->closeSessionLock($request);

        return response()->json([
            'status' => 'active',
            'remaining_seconds' => $remainingSeconds,
            'timeout_seconds' => $timeoutSeconds,
            'last_activity' => date('Y-m-d H:i:s', $lastActivity),
            'current_time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle auto-logout with reason tracking.
     */
    public function autoLogout(Request $request)
    {
        $reason = $request->input('reason', 'inactivity');
        $user = Auth::user();

        if ($user) {
            // Log the auto-logout event
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->full_name ?? $user->name,
                'user_role' => $user->role,
                'clinic_id' => $user->clinic_id,
                'action' => 'auto_logout',
                'description' => "User automatically logged out due to {$reason}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
            ]);
        }

        // Perform logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Store logout reason in flash session for display
        session()->flash('auto_logout_reason', $reason);

        return response()->json([
            'status' => 'logged_out',
            'message' => 'User has been logged out',
            'reason' => $reason,
            'redirect_url' => route('login')
        ]);
    }

    /**
     * Get auto-logout configuration for JavaScript.
     */
    public function getConfig()
    {
        $config = config('concure.auto_logout', []);

        // Get session lifetime from database (admin-configurable)
        $sessionLifetime = \DB::table('settings')
            ->whereNull('clinic_id')
            ->where('key', 'session_lifetime')
            ->value('value');

        // Use database value if available, otherwise fall back to config
        $timeoutMinutes = $sessionLifetime ? (int) $sessionLifetime : ($config['timeout_minutes'] ?? 10);

        // Warning should be 2 minutes before timeout, or 20% of timeout (whichever is smaller)
        $warningMinutes = min(2, (int) ($timeoutMinutes * 0.2));

        return response()->json([
            'enabled' => $config['enabled'] ?? true,
            'timeoutMinutes' => $timeoutMinutes,
            'warningMinutes' => $warningMinutes,
            'keepaliveInterval' => $config['keepalive_interval'] ?? 60,
            'timeoutSeconds' => $timeoutMinutes * 60,
            'warningSeconds' => $warningMinutes * 60,
        ]);
    }

    private function closeSessionLock(Request $request): void
    {
        if (!$request->hasSession()) {
            return;
        }

        try {
            $request->session()->save();
        } catch (\Throwable $e) {
            Log::warning('Failed to save session before releasing session activity lock.', [
                'error' => $e->getMessage(),
            ]);
        }

        if (function_exists('session_write_close') && session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }
    }
}

