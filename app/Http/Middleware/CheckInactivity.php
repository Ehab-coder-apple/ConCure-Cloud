<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckInactivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user has been inactive for more than 1 minute (for testing)
            $inactivityLimit = 1 * 60; // 1 minute in seconds
            
            if ($user->last_login_at) {
                $lastActivity = $user->last_login_at->timestamp;
                $currentTime = now()->timestamp;
                
                if (($currentTime - $lastActivity) > $inactivityLimit) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return redirect()->route('login')->with('message', 'You have been logged out due to inactivity.');
                }
            }
            
            // Update last activity time
            $user->last_login_at = now();
            $user->save();
        }
        
        return $next($request);
    }
}

