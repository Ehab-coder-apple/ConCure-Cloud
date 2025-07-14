<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrialMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Skip trial check for program owners
        if ($user->role === 'program_owner') {
            return $next($request);
        }

        // Check if user has a clinic
        if (!$user->clinic) {
            return redirect()->route('login')
                           ->with('error', 'No clinic associated with your account.');
        }

        $clinic = $user->clinic;

        // Check if clinic is on trial and trial has expired
        if ($clinic->is_trial && $clinic->isTrialExpired()) {
            // Allow access to subscription/upgrade pages
            $allowedRoutes = [
                'subscription.expired',
                'subscription.upgrade',
                'subscription.plans',
                'logout',
                'profile.edit',
                'settings.general',
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('subscription.expired')
                               ->with('error', 'Your 7-day free trial has expired. Please upgrade to continue using ConCure.');
            }
        }

        return $next($request);
    }
}
