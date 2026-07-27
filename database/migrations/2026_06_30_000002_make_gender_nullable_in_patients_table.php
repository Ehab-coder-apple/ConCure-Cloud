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
        // Use raw SQL to modify enum column to be nullable
        // This avoids Doctrine enum type issues
        DB::statement('ALTER TABLE `patients` MODIFY COLUMN `gender` ENUM("male", "female", "other") NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to NOT NULL
        DB::statement('ALTER TABLE `patients` MODIFY COLUMN `gender` ENUM("male", "female", "other") NOT NULL');
    }
};
