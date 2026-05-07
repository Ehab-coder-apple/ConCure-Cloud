<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SessionManagementService
{
    /**
     * Create a new session record and terminate any conflicting sessions
     */
    public static function createSession(User $user, string $credential, Request $request): UserSession
    {
        $deviceInfo = DeviceFingerprintService::getDeviceInfo($request);
        $sessionId = Session::getId();

        // Terminate any other active sessions for this user + credential combination
        $oldSessions = UserSession::forCredential($user->id, $credential)
            ->active()
            ->get();

        foreach ($oldSessions as $oldSession) {
            $oldSession->terminate('new_login_elsewhere', $sessionId);
            // Log the termination
            static::logSessionEvent($oldSession, 'session_terminated', 'Old session terminated due to new login');
        }

        // Create new session record
        $newSession = UserSession::create([
            'user_id' => $user->id,
            'credential_used' => $credential,
            'session_id' => $sessionId,
            'ip_address' => $deviceInfo['ip_address'],
            'device_fingerprint' => $deviceInfo['fingerprint'],
            'user_agent' => $deviceInfo['user_agent'],
            'browser' => $deviceInfo['browser'],
            'os' => $deviceInfo['os'],
        ]);

        // Log the new session creation
        static::logSessionEvent($newSession, 'session_created', 'User logged in');

        return $newSession;
    }

    /**
     * Terminate a session by session_id
     */
    public static function terminateSessionBySessionId(string $sessionId, string $reason): void
    {
        $session = UserSession::where('session_id', $sessionId)
            ->active()
            ->first();

        if ($session) {
            $session->terminate($reason);
            static::logSessionEvent($session, 'session_terminated', 'Session terminated: ' . $reason);
        }
    }

    /**
     * Check if a session is still active
     */
    public static function isSessionActive(string $sessionId): bool
    {
        $session = UserSession::where('session_id', $sessionId)
            ->first();

        return $session && $session->isActive();
    }

    /**
     * Log session events to audit trail
     */
    private static function logSessionEvent(UserSession $session, string $action, string $description): void
    {
        try {
            \App\Models\AuditLog::create([
                'user_id' => $session->user_id,
                'action' => $action,
                'description' => $description,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'performed_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if audit log can't be created
            \Log::warning('Failed to create audit log for session event', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
