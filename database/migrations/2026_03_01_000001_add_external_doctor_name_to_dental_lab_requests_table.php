<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_lab_requests', function (Blueprint $table) {
            // Make doctor_id nullable so external doctor can be used instead
            $table->foreignId('doctor_id')->nullable()->change();
        });

        if (!Schema::hasColumn('dental_lab_requests', 'external_doctor_name')) {
            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->string('external_doctor_name')->nullable()->after('doctor_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dental_lab_requests', 'external_doctor_name')) {
            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->dropColumn('external_doctor_name');
            });
        }
    }
};

