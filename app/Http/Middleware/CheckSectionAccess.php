<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSectionAccess
{
    /**
     * Ensure the authenticated user can access the requested section.
     */
    public function handle(Request $request, Closure $next, string $section): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if (!$user || !$user->canAccessSection($section)) {
            abort(403, 'Access denied. Insufficient permissions for this section.');
        }

        return $next($request);
    }
}