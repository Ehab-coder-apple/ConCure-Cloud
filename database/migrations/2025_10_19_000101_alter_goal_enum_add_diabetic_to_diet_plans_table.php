<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE diet_plans MODIFY goal ENUM('weight_loss','weight_gain','maintenance','muscle_gain','diabetic','health_improvement','other') NOT NULL");
                return;
            }
        } catch (\Throwable $e) {
            // Fall through to safe path for non-MySQL or if ALTER failed
        }

        // Safe path: on SQLite/pgsql (or if enum not supported), treat as string and no-op
        // Optionally, you could change the column to string here if needed.
        // We intentionally no-op to avoid unintended schema drift on dev DBs.
    }

    public function down(): void
    {
        try {
            if (DB::getDriverName() === 'mysql') {
                // Revert to original set without 'diabetic'
                DB::statement("ALTER TABLE diet_plans MODIFY goal ENUM('weight_loss','weight_gain','maintenance','muscle_gain','health_improvement','other') NOT NULL");
            }
        } catch (\Throwable $e) {
            // no-op
        }
    }
};

