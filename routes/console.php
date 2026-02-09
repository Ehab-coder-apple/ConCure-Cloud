<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('concure:setup', function () {
    $this->info('Setting up ConCure Clinic Management System...');
    
    // Create SQLite database file
    $dbPath = database_path('concure.sqlite');
    if (!file_exists($dbPath)) {
        touch($dbPath);
        $this->info('Created SQLite database file.');
    }
    
    // Run migrations
    $this->call('migrate');
    
    // Seed the database
    $this->call('db:seed');
    
    // Create storage symlink
    $this->call('storage:link');
    
    $this->info('ConCure setup completed successfully!');
})->purpose('Set up ConCure application');

Artisan::command('concure:expire-ads', function () {
    $expiredAds = \App\Models\Advertisement::where('expires_at', '<', now())
        ->where('is_active', true)
        ->update(['is_active' => false]);
    
    $this->info("Expired {$expiredAds} advertisements.");
})->purpose('Expire advertisements that have passed their expiration date');

Artisan::command('concure:cleanup-files', function () {
    // Clean up temporary files older than 7 days
    $tempPath = storage_path('app/temp');
    if (is_dir($tempPath)) {
        $files = glob($tempPath . '/*');
        $cleaned = 0;
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < strtotime('-7 days')) {
                unlink($file);
                $cleaned++;
            }
        }
        $this->info("Cleaned up {$cleaned} temporary files.");
    }
})->purpose('Clean up temporary files older than 7 days');
