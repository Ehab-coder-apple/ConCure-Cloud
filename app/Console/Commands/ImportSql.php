<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportSql extends Command
{
    protected $signature = 'sql:import {file} {clinic_id} {job_id}';
    protected $description = 'Import a SQL file into the database (runs in background)';

    public function handle()
    {
        $filePath = $this->argument('file');
        $clinicId = $this->argument('clinic_id');
        $jobId = $this->argument('job_id');
        $statusFile = storage_path("app/sql_import_{$jobId}.json");

        // Update status: running
        $this->updateStatus($statusFile, 'running', 'Import started...');

        if (!file_exists($filePath)) {
            $this->updateStatus($statusFile, 'failed', 'SQL file not found.');
            Log::error('SQL Import: file not found', ['file' => $filePath]);
            return 1;
        }

        $startTime = microtime(true);

        try {
            $sql = file_get_contents($filePath);

            // Prepend optimizations
            $optimizedSql = "SET FOREIGN_KEY_CHECKS=0;\nSET UNIQUE_CHECKS=0;\nSET AUTOCOMMIT=0;\n"
                . $sql
                . "\nCOMMIT;\nSET FOREIGN_KEY_CHECKS=1;\nSET UNIQUE_CHECKS=1;\nSET AUTOCOMMIT=1;\n";

            unset($sql); // Free memory

            Log::info('SQL Import command started', [
                'clinic_id' => $clinicId,
                'job_id' => $jobId,
                'file_size' => filesize($filePath),
            ]);

            // Execute via PDO directly (no timeout issues in CLI)
            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(\PDO::ATTR_TIMEOUT, 600);
            $pdo->exec($optimizedSql);

            $elapsed = round(microtime(true) - $startTime, 2);

            // Clean up the uploaded SQL file
            @unlink($filePath);

            $this->updateStatus($statusFile, 'completed', "Import completed in {$elapsed}s.", $elapsed);

            Log::info('SQL Import command completed', [
                'clinic_id' => $clinicId,
                'job_id' => $jobId,
                'seconds' => $elapsed,
            ]);

            $this->info("Import completed in {$elapsed} seconds.");
            return 0;

        } catch (\Exception $e) {
            $elapsed = round(microtime(true) - $startTime, 2);

            // Try to reset MySQL session state
            try {
                $pdo = DB::connection()->getPdo();
                $pdo->exec('ROLLBACK');
                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
                $pdo->exec('SET UNIQUE_CHECKS=1');
                $pdo->exec('SET AUTOCOMMIT=1');
            } catch (\Exception $ignore) {}

            // Clean up
            @unlink($filePath);

            $error = $e->getMessage();
            $this->updateStatus($statusFile, 'failed', "Import failed after {$elapsed}s: {$error}", $elapsed);

            Log::error('SQL Import command failed', [
                'clinic_id' => $clinicId,
                'job_id' => $jobId,
                'error' => $error,
                'seconds' => $elapsed,
            ]);

            $this->error("Import failed: {$error}");
            return 1;
        }
    }

    private function updateStatus(string $statusFile, string $status, string $message, ?float $elapsed = null): void
    {
        $data = [
            'status' => $status,
            'message' => $message,
            'updated_at' => now()->toIso8601String(),
        ];
        if ($elapsed !== null) {
            $data['elapsed'] = $elapsed;
        }
        file_put_contents($statusFile, json_encode($data));
    }
}

