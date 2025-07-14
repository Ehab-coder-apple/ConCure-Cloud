<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterAuthController;
use Symfony\Component\HttpFoundation\Response;

class MasterGuest
{
    /**
     * Handle an incoming request for master guest access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (MasterAuthController::check()) {
            return redirect()->route('master.dashboard');
        }

        return $next($request);
    }
}
