<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Restore the user's preferred locale from:
     * 1. Session (set by LanguageController)
     * 2. Authenticated user's `language` column
     * 3. Fall back to config default
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // Priority 1: session value (set by language switcher)
        if ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }
        // Priority 2: authenticated user's saved language preference
        elseif (Auth::check() && Auth::user()->language) {
            $locale = Auth::user()->language;
            // Sync to session so it persists
            $request->session()->put('locale', $locale);
        }

        // Validate against supported languages
        if ($locale) {
            $supported = array_keys(config('concure.supported_languages', ['en' => 'English']));
            if (in_array($locale, $supported)) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}

