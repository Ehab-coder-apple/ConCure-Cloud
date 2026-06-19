<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE aesthetic_sessions MODIFY status ENUM('scheduled','started','completed','cancelled','no_show') DEFAULT 'scheduled'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE aesthetic_sessions MODIFY status ENUM('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled'");
        }
    }
};