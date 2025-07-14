<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivationMiddleware
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

        // Check if user is activated
        if (!$user->activated_at) {
            return redirect()->route('activation.required')
                           ->with('error', 'Your account requires activation. Please contact your administrator.');
        }

        // Check if user is active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                           ->with('error', 'Your account has been deactivated. Please contact your administrator.');
        }

        // Check clinic status for non-program-owner users
        if ($user->role !== 'program_owner' && $user->clinic) {
            if (!$user->clinic->is_active) {
                auth()->logout();
                return redirect()->route('login')
                               ->with('error', 'Your clinic has been deactivated. Please contact support.');
            }

            if (!$user->clinic->activated_at) {
                return redirect()->route('clinic.activation.required')
                               ->with('error', 'Your clinic requires activation. Please contact support.');
            }

            // Check subscription expiry
            if ($user->clinic->subscription_expires_at && $user->clinic->subscription_expires_at->isPast()) {
                return redirect()->route('subscription.expired')
                               ->with('error', 'Your clinic subscription has expired. Please renew to continue.');
            }

            // Check trial expiry
            if ($user->clinic->is_trial && $user->clinic->isTrialExpired()) {
                return redirect()->route('subscription.expired')
                               ->with('error', 'Your 7-day free trial has expired. Please upgrade to continue using ConCure.');
            }
        }

        return $next($request);
    }
}
