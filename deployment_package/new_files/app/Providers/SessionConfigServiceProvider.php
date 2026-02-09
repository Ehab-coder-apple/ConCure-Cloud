<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SessionConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load session lifetime from database if available
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $sessionLifetime = \Illuminate\Support\Facades\DB::table('settings')
                    ->whereNull('clinic_id')
                    ->where('key', 'session_lifetime')
                    ->value('value');

                if ($sessionLifetime !== null) {
                    config(['session.lifetime' => (int) $sessionLifetime]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not available yet (e.g., during migrations)
            \Illuminate\Support\Facades\Log::debug('Could not load session lifetime from database: ' . $e->getMessage());
        }
    }
}
