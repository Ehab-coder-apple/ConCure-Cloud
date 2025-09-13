<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('master.login');
        }

        // Check if user is super admin
        if (!Auth::user()->isSuperAdmin()) {
            Auth::logout();
            return redirect()->route('master.login')->withErrors([
                'email' => 'Access denied. Super admin privileges required.'
            ]);
        }

        // Check if user is active
        if (!Auth::user()->is_active) {
            Auth::logout();
            return redirect()->route('master.login')->withErrors([
                'email' => 'Your account has been deactivated.'
            ]);
        }

        return $next($request);
    }
}
