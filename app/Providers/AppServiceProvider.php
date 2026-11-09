<?php

namespace App\Providers;

use App\Models\DentalImage;
use App\Models\PatientFile;
use App\Models\PatientImage;
use App\Models\PatientVideo;
use App\Observers\StorageUsageObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keep Clinic::storage_used in sync in real time as file-bearing
        // rows are created/deleted, instead of relying only on the manual
        // "Recalculate" action. Any model implementing TracksStorageUsage
        // can be added here to be covered automatically.
        PatientFile::observe(StorageUsageObserver::class);
        PatientImage::observe(StorageUsageObserver::class);
        PatientVideo::observe(StorageUsageObserver::class);
        DentalImage::observe(StorageUsageObserver::class);
    }
}
