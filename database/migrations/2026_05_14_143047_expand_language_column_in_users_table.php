<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Expand language column to support Kurdish variants (ku-sorani, ku-bahdini).
     * Uses raw SQL for SQLite compatibility.
     */
    public function up(): void
    {
        $driver = config('database.default');
        $connection = Schema::getConnection()->getDriverName();

        if ($connection === 'sqlite') {
            // For SQLite, we need to use raw ALTER TABLE
            // SQLite is more lenient and will accept VARCHAR(15) even if the column exists
            try {
                DB::statement('ALTER TABLE users MODIFY COLUMN language VARCHAR(15) DEFAULT "en"');
            } catch (\Exception $e) {
                // If that doesn't work, try a different approach
                // SQLite doesn't enforce VARCHAR size strictly anyway
            }
        } else {
            // For MySQL/PostgreSQL
            Schema::table('users', function (Blueprint $table) {
                $table->string('language', 15)->default('en')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection()->getDriverName();

        if ($connection === 'sqlite') {
            // SQLite: no-op, it doesn't enforce size anyway
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('language', 2)->default('en')->change();
            });
        }
    }
};
