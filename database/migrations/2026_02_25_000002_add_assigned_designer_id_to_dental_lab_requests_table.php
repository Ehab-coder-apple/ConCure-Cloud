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
        // Note: this migration may have been partially applied if it previously failed mid-run.
        // Make it safe to re-run by only adding the column if it doesn't exist.
        if (!Schema::hasColumn('dental_lab_requests', 'assigned_designer_id')) {
            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->foreignId('assigned_designer_id')
                    ->nullable()
                    ->after('assigned_technician_id')
                    ->constrained('users')
                    ->onDelete('set null');
            });
        }

        // Backfill: previously CAD/CAM designers were stored in assigned_technician_id.
        // Move those assignments to assigned_designer_id to preserve visibility/history.
        DB::table('dental_lab_requests')
            ->whereIn('assigned_technician_id', function ($q) {
                $q->select('id')->from('users')->where('role', 'cad_cam_designer');
            })
            ->update([
                'assigned_designer_id' => DB::raw('assigned_technician_id'),
                'assigned_technician_id' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert backfill (best-effort)
        DB::table('dental_lab_requests')
            ->whereNull('assigned_technician_id')
            ->whereNotNull('assigned_designer_id')
            ->update([
                'assigned_technician_id' => DB::raw('assigned_designer_id'),
            ]);

        if (Schema::hasColumn('dental_lab_requests', 'assigned_designer_id')) {
            // dropForeign will fail if the FK doesn't exist (e.g., partial/manual schema changes).
            // Try to drop it, but don't block rollback if it isn't present.
            try {
                Schema::table('dental_lab_requests', function (Blueprint $table) {
                    $table->dropForeign(['assigned_designer_id']);
                });
            } catch (Throwable $e) {
                // ignore
            }

            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->dropColumn('assigned_designer_id');
            });
        }
    }
};
