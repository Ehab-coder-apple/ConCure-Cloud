<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_inventory_usage', function (Blueprint $table) {
            $table->string('tenant_id', 20)->nullable()->after('session_id');
            $table->index('tenant_id');
        });

        // Backfill from parent sessions
        DB::statement('
            UPDATE session_inventory_usage AS siu
            JOIN aesthetic_sessions AS s ON siu.session_id = s.id
            SET siu.tenant_id = s.tenant_id
            WHERE siu.tenant_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('session_inventory_usage', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
