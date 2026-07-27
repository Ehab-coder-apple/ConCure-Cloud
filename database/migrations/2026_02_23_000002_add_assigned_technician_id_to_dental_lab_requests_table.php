<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dental_lab_requests', function (Blueprint $table) {
            $table->foreignId('assigned_technician_id')
                ->nullable()
                ->after('doctor_id')
                ->constrained('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dental_lab_requests', function (Blueprint $table) {
            $table->dropForeign(['assigned_technician_id']);
            $table->dropColumn('assigned_technician_id');
        });
    }
};
