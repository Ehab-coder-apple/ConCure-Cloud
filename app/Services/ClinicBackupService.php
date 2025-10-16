<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;

class ClinicBackupService
{
    protected string $baseBackupDir;

    public function __construct()
    {
        $this->baseBackupDir = storage_path('app/backups');
    }

    /**
     * Generate backup for a clinic: DB JSON + files, zipped.
     * Returns array with: [path, size, last_backup_at]
     */
    public function generateBackupForClinic(int $clinicId, string $type = 'manual', ?int $createdBy = null, int $retentionDays = 30): array
    {
        $clinic = Clinic::findOrFail($clinicId);

        $timestamp = now()->format('Ymd_His');
        $clinicDir = $this->baseBackupDir . '/clinic-' . $clinicId;
        $workDir = $clinicDir . '/tmp_' . $timestamp . '_' . Str::random(6);
        $dbDir = $workDir . '/database';
        $filesDir = $workDir . '/files';

        // Ensure dirs
        @mkdir($dbDir, 0755, true);
        @mkdir($filesDir, 0755, true);

        // Record log row (pending)
        $backupId = DB::table('clinic_backups')->insertGetId([
            'clinic_id' => $clinicId,
            'type' => $type,
            'status' => 'pending',
            'disk' => config('filesystems.default', 'local'),
            'path' => '',
            'size_bytes' => null,
            'created_by' => $createdBy,
            'meta' => json_encode(['started_at' => now()->toDateTimeString()]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Export DB data per table into JSON
            $exported = $this->exportDatabase($clinicId, $dbDir);

            // Collect files
            $this->collectClinicFiles($clinicId, $filesDir);

            // Create ZIP
            $zipPath = $clinicDir . '/' . $timestamp . '_clinic-' . $clinicId . '.zip';
            $this->zipDirectory($workDir, $zipPath);
            $size = file_exists($zipPath) ? filesize($zipPath) : 0;

            // Update log as success
            DB::table('clinic_backups')->where('id', $backupId)->update([
                'status' => 'success',
                'path' => $zipPath,
                'size_bytes' => $size,
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

            // Retention cleanup
            $this->applyRetention($clinicId, $retentionDays);

            // Cleanup temp dir
            $this->rrmdir($workDir);

            return ['path' => $zipPath, 'size' => $size, 'last_backup_at' => now()];
        } catch (\Throwable $e) {
            // Mark failed
            DB::table('clinic_backups')->where('id', $backupId)->update([
                'status' => 'failed',
                'meta' => json_encode(['error' => $e->getMessage(), 'trace' => substr($e->getTraceAsString(), 0, 2000)]),
                'updated_at' => now(),
            ]);
            // Cleanup
            $this->rrmdir($workDir);
            throw $e;
        }
    }

    protected function exportDatabase(int $clinicId, string $dbDir): array
    {
        $exported = [];
        $tables = $this->listTables();

        $skip = [
            'migrations','failed_jobs','password_reset_tokens','personal_access_tokens','cache','jobs','job_batches','sessions','telescope_entries','telescope_entries_tags','telescope_monitoring'
        ];

        foreach ($tables as $table) {
            if (in_array($table, $skip, true)) { continue; }
            try {
                $rows = $this->fetchRowsForClinic($table, $clinicId);
                if ($rows === null) { continue; } // Table not clinic scoped
                $path = $dbDir . '/' . $table . '.json';
                file_put_contents($path, json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
                $exported[] = $table;
            } catch (\Throwable $e) {
                // Ignore tables that fail (e.g., missing columns) but continue
            }
        }
        // Also export clinic settings via settings table if present
        if (Schema::hasTable('settings')) {
            $settings = DB::table('settings')->where('clinic_id', $clinicId)->get();
            file_put_contents($dbDir . '/settings.json', json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        }
        return $exported;
    }

    protected function fetchRowsForClinic(string $table, int $clinicId): ?array
    {
        // If table has clinic_id
        if ($this->tableHasColumn($table, 'clinic_id')) {
            return DB::table($table)->where('clinic_id', $clinicId)->get()->map(fn($r)=> (array)$r)->all();
        }
        // Patient scoped
        if ($this->tableHasColumn($table, 'patient_id') && Schema::hasTable('patients')) {
            return DB::table($table)
                ->join('patients','patients.id','=',$table.'.patient_id')
                ->where('patients.clinic_id', $clinicId)
                ->select($table.'.*')
                ->get()->map(fn($r)=> (array)$r)->all();
        }
        // Diet plan scoped
        if ($this->tableHasColumn($table, 'diet_plan_id') && Schema::hasTable('diet_plans') && Schema::hasTable('patients')) {
            return DB::table($table)
                ->join('diet_plans','diet_plans.id','=',$table.'.diet_plan_id')
                ->join('patients','patients.id','=','diet_plans.patient_id')
                ->where('patients.clinic_id', $clinicId)
                ->select($table.'.*')
                ->get()->map(fn($r)=> (array)$r)->all();
        }
        if ($this->tableHasColumn($table, 'diet_plan_meal_id') && Schema::hasTable('diet_plan_meals') && Schema::hasTable('diet_plans') && Schema::hasTable('patients')) {
            return DB::table($table)
                ->join('diet_plan_meals','diet_plan_meals.id','=',$table.'.diet_plan_meal_id')
                ->join('diet_plans','diet_plans.id','=','diet_plan_meals.diet_plan_id')
                ->join('patients','patients.id','=','diet_plans.patient_id')
                ->where('patients.clinic_id', $clinicId)
                ->select($table.'.*')
                ->get()->map(fn($r)=> (array)$r)->all();
        }
        // User scoped
        if ($this->tableHasColumn($table, 'user_id') && Schema::hasTable('users')) {
            return DB::table($table)
                ->join('users','users.id','=',$table.'.user_id')
                ->where('users.clinic_id', $clinicId)
                ->select($table.'.*')
                ->get()->map(fn($r)=> (array)$r)->all();
        }
        if ($this->tableHasColumn($table, 'doctor_id') && Schema::hasTable('users')) {
            return DB::table($table)
                ->join('users','users.id','=',$table.'.doctor_id')
                ->where('users.clinic_id', $clinicId)
                ->select($table.'.*')
                ->get()->map(fn($r)=> (array)$r)->all();
        }
        // Appointment scoped
        if ($this->tableHasColumn($table, 'appointment_id') && Schema::hasTable('appointments')) {
            return DB::table($table)
                ->join('appointments','appointments.id','=',$table.'.appointment_id')
                ->where('appointments.clinic_id', $clinicId)
                ->select($table.'.*')
                ->get()->map(fn($r)=> (array)$r)->all();
        }
        // Invoice scoped
        if ($this->tableHasColumn($table, 'invoice_id') && Schema::hasTable('invoices')) {
            return DB::table($table)
                ->join('invoices','invoices.id','=',$table.'.invoice_id')
                ->where('invoices.clinic_id', $clinicId)
                ->select($table.'.*')
                ->get()->map(fn($r)=> (array)$r)->all();
        }
        // If none matched, skip exporting this table
        return null;
    }

    protected function tableHasColumn(string $table, string $column): bool
    {
        try {
            return in_array($column, Schema::getColumnListing($table), true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function listTables(): array
    {
        $driver = DB::getDriverName();
        try {
            if ($driver === 'mysql') {
                $dbname = DB::getDatabaseName();
                $rows = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = ?", [$dbname]);
                return array_map(fn($r) => $r->table_name ?? array_values((array)$r)[0], $rows);
            } elseif ($driver === 'sqlite') {
                $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
                return array_map(fn($r) => $r->name ?? array_values((array)$r)[0], $rows);
            } elseif ($driver === 'pgsql') {
                $rows = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                return array_map(fn($r) => $r->tablename ?? array_values((array)$r)[0], $rows);
            }
        } catch (\Throwable $e) {
            // Fallback to known tables when listing fails
        }
        // Minimal fallback
        return ['clinics','users','patients','appointments','foods','food_groups','diet_plans','diet_plan_meals','diet_plan_meal_foods','diet_plan_weight_records','invoices','expenses','receipts'];
    }

    protected function collectClinicFiles(int $clinicId, string $filesDir): void
    {
        $publicRoot = storage_path('app/public');

        // 1) Receipts files: receipts/{clinic_id}/files
        $this->copyIfExists($publicRoot . '/receipts/' . $clinicId . '/files', $filesDir . '/receipts/' . $clinicId . '/files');

        // 2) Advertisements: advertisements/{clinic_id}
        $this->copyIfExists($publicRoot . '/advertisements/' . $clinicId, $filesDir . '/advertisements/' . $clinicId);

        // 3) Patient files: patients/{id}/files for clinic patients
        if (Schema::hasTable('patients')) {
            $patientIds = DB::table('patients')->where('clinic_id', $clinicId)->pluck('id')->all();
            foreach ($patientIds as $pid) {
                $this->copyIfExists($publicRoot . '/patients/' . $pid . '/files', $filesDir . '/patients/' . $pid . '/files');
            }
        }

        // 4) Clinic logo if stored in public disk under clinic-logos
        $logoRel = null;
        if (Schema::hasTable('settings')) {
            $logoRel = DB::table('settings')->where('clinic_id', $clinicId)->where('key','clinic_logo')->value('value');
        }
        if ($logoRel) {
            $logoRel = ltrim(str_replace(['storage/','public/'], '', $logoRel), '/');
            $this->copyIfExists($publicRoot . '/' . $logoRel, $filesDir . '/clinic-logos/' . basename($logoRel));
        }
    }

    protected function copyIfExists(string $src, string $dst): void
    {
        if (is_file($src)) {
            @mkdir(dirname($dst), 0755, true);
            @copy($src, $dst);
        } elseif (is_dir($src)) {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($it as $item) {
                $target = $dst . DIRECTORY_SEPARATOR . $it->getSubPathName();
                if ($item->isDir()) {
                    @mkdir($target, 0755, true);
                } else {
                    @mkdir(dirname($target), 0755, true);
                    @copy($item->getPathname(), $target);
                }
            }
        }
    }

    protected function zipDirectory(string $dir, string $zipPath): void
    {
        @mkdir(dirname($zipPath), 0755, true);
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create ZIP archive');
        }
        $rootLen = strlen($dir) + 1;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            $filePath = $file->getPathname();
            $localName = substr($filePath, $rootLen);
            $zip->addFile($filePath, $localName);
        }
        $zip->close();
    }

    protected function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) { return; }
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
        }
        @rmdir($dir);
    }

    public function getLastBackupForClinic(int $clinicId)
    {
        return DB::table('clinic_backups')
            ->where('clinic_id', $clinicId)
            ->where('status', 'success')
            ->orderByDesc('completed_at')
            ->first();
    }

    public function getRecentBackups(int $clinicId, int $limit = 10)
    {
        return DB::table('clinic_backups')
            ->where('clinic_id', $clinicId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function applyRetention(int $clinicId, int $retentionDays = 30): void
    {
        if ($retentionDays <= 0) { return; }
        $cutoff = now()->subDays($retentionDays);
        $rows = DB::table('clinic_backups')
            ->where('clinic_id', $clinicId)
            ->where('created_at', '<', $cutoff)
            ->get();
        foreach ($rows as $row) {
            if (!empty($row->path) && file_exists($row->path)) {
                @unlink($row->path);
            }
        }
        DB::table('clinic_backups')
            ->where('clinic_id', $clinicId)
            ->where('created_at', '<', $cutoff)
            ->delete();
    }
}

