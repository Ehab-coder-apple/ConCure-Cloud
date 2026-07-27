<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN `type` ENUM(
                'consultation',
                'follow_up',
                'checkup',
                'procedure',
                'emergency',
                'routine_checkup',
                'other'
            ) NOT NULL DEFAULT 'consultation'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Revert emergency/routine_checkup to 'other' before shrinking the enum
            DB::table('appointments')
                ->whereIn('type', ['emergency', 'routine_checkup'])
                ->update(['type' => 'other']);

            DB::statement("ALTER TABLE appointments MODIFY COLUMN `type` ENUM(
                'consultation',
                'follow_up',
                'checkup',
                'procedure',
                'other'
            ) NOT NULL DEFAULT 'consultation'");
        }
    }
};

