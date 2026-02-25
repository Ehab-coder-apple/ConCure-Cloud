<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dental_lab_requests', function (Blueprint $table) {
            $table->foreignId('assigned_designer_id')
                ->nullable()
                ->after('assigned_technician_id')
                ->constrained('users')
                ->onDelete('set null');
        });

        // Backfill: previously CAD/CAM designers were stored in assigned_technician_id.
        // Move those assignments to assigned_designer_id to preserve visibility/history.
        DB::statement("\
            UPDATE dental_lab_requests
            SET assigned_designer_id = assigned_technician_id,
                assigned_technician_id = NULL
            WHERE assigned_technician_id IN (SELECT id FROM users WHERE role = 'cad_cam_designer')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert backfill (best-effort)
        DB::statement("\
            UPDATE dental_lab_requests
            SET assigned_technician_id = assigned_designer_id
            WHERE assigned_technician_id IS NULL
              AND assigned_designer_id IS NOT NULL
        ");

        Schema::table('dental_lab_requests', function (Blueprint $table) {
            $table->dropForeign(['assigned_designer_id']);
            $table->dropColumn('assigned_designer_id');
        });
    }
};
