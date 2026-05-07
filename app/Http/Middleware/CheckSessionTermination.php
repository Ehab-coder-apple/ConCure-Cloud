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
        // Only check authenticated users
        if (Auth::check()) {
            $sessionId = Session::getId();
            
            // Check if current session has been terminated
            $userSession = UserSession::where('session_id', $sessionId)->first();
            
            if ($userSession && !$userSession->isActive()) {
                // Session has been terminated
                Auth::logout();
                Session::flush();
                
                // Redirect to login with termination message
                return redirect()->route('login')
                    ->with('session_terminated', true)
                    ->with('termination_message', 'For your security, your session has been terminated. You were logged out because you signed in from another device.');
            }
        }

        return $next($request);
    }
}
