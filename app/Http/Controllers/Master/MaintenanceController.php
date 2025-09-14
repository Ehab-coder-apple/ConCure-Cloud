<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MaintenanceController extends Controller
{
    /**
     * One-time server update: fetch fixed migration files from GitHub, write them,
     * run migrate --force, ensure view cache dir exists, and optimize:clear.
     */
    public function runServerUpdate(Request $request)
    {
        // Extra safety: only allow super admin (middleware should already enforce)
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied');
        }

        $files = [
            'database/migrations/2025_09_14_000002_add_subscription_fields_to_clinics_table.php' => 'https://raw.githubusercontent.com/Ehab-coder-apple/ConCure-Cloud/main/database/migrations/2025_09_14_000002_add_subscription_fields_to_clinics_table.php',
            'database/migrations/2025_09_14_000004_alter_billing_cycle_enum_on_clinics_table.php' => 'https://raw.githubusercontent.com/Ehab-coder-apple/ConCure-Cloud/main/database/migrations/2025_09_14_000004_alter_billing_cycle_enum_on_clinics_table.php',
            'database/migrations/2025_09_14_000005_ensure_subscription_plans_table_exists.php' => 'https://raw.githubusercontent.com/Ehab-coder-apple/ConCure-Cloud/main/database/migrations/2025_09_14_000005_ensure_subscription_plans_table_exists.php',
        ];

        $results = [];
        foreach ($files as $relativePath => $url) {
            try {
                $resp = Http::timeout(15)->get($url);
                if (!$resp->ok()) {
                    $results[] = "Failed to fetch $relativePath (HTTP ".$resp->status().")";
                    continue;
                }
                $fullPath = base_path($relativePath);
                $dir = dirname($fullPath);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                $bytes = file_put_contents($fullPath, $resp->body(), LOCK_EX);
                if ($bytes === false) {
                    $results[] = "Failed to write $relativePath";
                } else {
                    $results[] = "Updated $relativePath (".$bytes." bytes)";
                }
            } catch (\Throwable $e) {
                Log::error('Server update fetch failed', ['file' => $relativePath, 'error' => $e->getMessage()]);
                $results[] = "Exception updating $relativePath: ".$e->getMessage();
            }
        }

        // Ensure compiled views dir exists to avoid optimize:clear failure
        $viewsDir = storage_path('framework/views');
        if (!is_dir($viewsDir)) {
            @mkdir($viewsDir, 0755, true);
        }

        // Run migrations and clear caches
        try {
            Artisan::call('migrate', ['--force' => true]);
            $results[] = 'Migrate output: '.trim(Artisan::output());
        } catch (\Throwable $e) {
            $results[] = 'Migrate error: '.$e->getMessage();
        }

        try {
            Artisan::call('optimize:clear');
            $results[] = 'Optimize:clear output: '.trim(Artisan::output());
        } catch (\Throwable $e) {
            $results[] = 'Optimize:clear error: '.$e->getMessage();
        }

        return redirect()->back()->with('status', 'Server update completed')->with('server_update_results', $results);
    }
}

