<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_lab_requests', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->change();
        });

        if (!Schema::hasColumn('dental_lab_requests', 'external_patient_name')) {
            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->string('external_patient_name')->nullable()->after('patient_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dental_lab_requests', 'external_patient_name')) {
            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->dropColumn('external_patient_name');
            });
        }
    }
};
