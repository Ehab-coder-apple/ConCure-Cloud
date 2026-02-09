<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // DEVELOPMENT MODE: Disable all role checks
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            // Only check if user is authenticated
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            return $next($request);
        }

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        // Check if user is active and activated
        if (!$user->isActiveAndActivated()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account is not active or not activated.');
        }

        // Check clinic status for all clinic users
        if ($user->clinic) {
            if (!$user->clinic->isActiveWithValidSubscription()) {
                auth()->logout();
                return redirect()->route('login')->with('error', 'Clinic is inactive.');
            }
        }

        return $next($request);
    }
}
