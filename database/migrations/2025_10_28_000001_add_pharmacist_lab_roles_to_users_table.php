<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend enum values on MySQL. On SQLite/others, UI filtering won't apply, so no-op.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','doctor','assistant','nurse','accountant','patient','nutritionist','pharmacist','lab_dept') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Revert to the previous set without pharmacist/lab_dept
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','doctor','assistant','nurse','accountant','patient','nutritionist') NOT NULL");
        }
    }
};

