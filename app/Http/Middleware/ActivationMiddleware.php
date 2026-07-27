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

        // Check clinic status for all users (program_owner role removed)
        if ($user->clinic) {
            if (!$user->clinic->is_active) {
                auth()->logout();
                return redirect()->route('login')
                               ->with('error', 'Your clinic has been deactivated. Please contact support.');
            }

            if (!$user->clinic->activated_at) {
                return redirect()->route('clinic.activation.required')
                               ->with('error', 'Your clinic requires activation. Please contact support.');
            }

            // Ensure the clinic has a tenant_id backfilled as early as possible.
            // Several modules (e.g. Aesthetic) scope their data by tenant_id via
            // Auth::user()->accessibleTenantIds(), which resolves to an empty
            // set (hiding all data, including dropdown options) when the
            // clinic's tenant_id column has never been generated.
            if (empty($user->clinic->tenant_id)) {
                $user->clinic->ensureTenantId();
            }

            // Subscription checks removed - no longer needed

            // Trial system removed - no longer needed
        }

        return $next($request);
    }
}
