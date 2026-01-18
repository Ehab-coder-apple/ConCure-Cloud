<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetClinicTimezone
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
            
            // Get clinic timezone from settings
            if ($user->clinic_id) {
                $timezone = DB::table('settings')
                    ->where('clinic_id', $user->clinic_id)
                    ->where('key', 'timezone')
                    ->value('value');
                
                if ($timezone) {
                    // Set the timezone for this request
                    config(['app.timezone' => $timezone]);
                    date_default_timezone_set($timezone);
                }
            }
        }
        
        return $next($request);
    }
}

