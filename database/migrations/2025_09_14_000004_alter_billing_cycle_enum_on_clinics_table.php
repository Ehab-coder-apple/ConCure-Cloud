<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Expand billing_cycle options to include quarterly and semiannual.
        // MySQL: alter ENUM; Other drivers (sqlite/pgsql): treat as string/text and no-op safely.
        try {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE clinics MODIFY billing_cycle ENUM('monthly','quarterly','semiannual','yearly') NOT NULL DEFAULT 'monthly'");
                return;
            }
        } catch (\Throwable $e) {
            // Fall through to safe path
        }

        // Safe path for non-MySQL or if ALTER failed: ensure column exists with default and backfill nulls.
        if (Schema::hasTable('clinics')) {
            if (!Schema::hasColumn('clinics', 'billing_cycle')) {
                Schema::table('clinics', function (Blueprint $table) {
                    $table->string('billing_cycle')->default('monthly');
                });
            }
            // Backfill nulls to 'monthly' just in case
            try {
                DB::table('clinics')->whereNull('billing_cycle')->update(['billing_cycle' => 'monthly']);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function down(): void
    {
        // Revert to only monthly/yearly in MySQL; otherwise no-op.
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE clinics MODIFY billing_cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly'");
            }
        } catch (\Throwable $e) {
            // no-op for non-MySQL or failure
        }
    }
};
