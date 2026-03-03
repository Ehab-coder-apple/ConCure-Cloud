<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Switch the application language.
     */
    public function switch(Request $request, string $language)
    {
        // Validate language against supported languages
        $supportedLanguages = array_keys(config('concure.supported_languages', ['en' => 'English', 'ar' => 'العربية', 'ku' => 'کوردی']));

        if (!in_array($language, $supportedLanguages)) {
            abort(404, 'Language not supported');
        }

        // Set session locale
        session(['locale' => $language]);
        
        // Update user's language preference if authenticated
        if (auth()->check()) {
            auth()->user()->update(['language' => $language]);
        }

        // Set application locale
        app()->setLocale($language);

        return back()->with('success', 'Language changed successfully.');
    }
}
