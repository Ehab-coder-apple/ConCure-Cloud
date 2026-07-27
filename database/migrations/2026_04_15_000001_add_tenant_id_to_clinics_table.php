<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add tenant_id to clinics table for multi-clinic organizations.
     * Pharmacists and other cross-clinic roles can access data from all clinics
     * within their tenant.
     */
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->string('tenant_id', 50)->nullable()->after('id')->index();
        });

        // Set a default tenant_id for existing clinics (each clinic gets its own tenant initially)
        // This maintains backward compatibility - existing clinics operate independently
        DB::statement("UPDATE clinics SET tenant_id = CONCAT('TEN-', id) WHERE tenant_id IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
};
