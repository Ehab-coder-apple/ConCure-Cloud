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
        Schema::table('aesthetic_sessions', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('patient_package_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('treatment_id')->nullable()->after('patient_id')->constrained('aesthetic_treatments')->onDelete('cascade');
        });

        // Make patient_package_id nullable
        Schema::table('aesthetic_sessions', function (Blueprint $table) {
            $table->foreignId('patient_package_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aesthetic_sessions', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['treatment_id']);
            $table->dropColumn(['patient_id', 'treatment_id']);
        });

        Schema::table('aesthetic_sessions', function (Blueprint $table) {
            $table->foreignId('patient_package_id')->nullable(false)->change();
        });
    }
};
