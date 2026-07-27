<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTermination
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip session termination check for contract routes
        if ($request->is('contract/*')) {
            return $next($request);
        }

        // Only check authenticated users
        // Skip session termination check for contract routes
        if ($request->is("contract/*")) {
            return $next($request);
        }

        if (Auth::check()) {
            try {
                $sessionId = Session::getId();
                $userId = Auth::id();

                // Check if current session has been terminated
                $userSession = UserSession::where('session_id', $sessionId)
                    ->where('user_id', $userId)
                    ->first();

                if ($userSession && !$userSession->isActive()) {
                    // Capture user's locale before logout
                    $userLocale = Auth::user()->language ?? 'en';

                    // Session has been terminated - log out immediately
                    Auth::logout();
                    Session::flush();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    // Redirect to login with termination message and locale
                    return redirect()->route('login')
                        ->with('session_terminated', true)
                        ->with('user_locale', $userLocale)
                        ->with('termination_message', 'For your security, your session has been terminated. You were logged out because you signed in from another device.');
                }

                // If no session record found, the session might be orphaned
                // This shouldn't happen in normal operation
                if (!$userSession) {
                    // Silently allow - the user is authenticated by Laravel's session
                    // The session record will be created on next login
                }
            } catch (\Exception $e) {
                // Don't break the application on middleware errors
                \Log::warning('CheckSessionTermination: Error checking session', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $next($request);
    }
}
