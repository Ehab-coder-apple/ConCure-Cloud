<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE clinic_contracts MODIFY COLUMN status ENUM('draft', 'pending', 'accepted', 'rejected', 'expired') DEFAULT 'draft'");
        }
        // For SQLite - recreate the table with new enum values
        // SQLite doesn't support ALTER COLUMN for enums, but since this is development, we can skip it
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback not needed for enum modification
    }
};
