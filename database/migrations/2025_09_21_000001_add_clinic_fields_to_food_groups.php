<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('food_groups', 'clinic_id')) {
                $table->unsignedBigInteger('clinic_id')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('food_groups', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('clinic_id');
            }

            $table->index(['clinic_id', 'is_active']);

            try {
                $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            } catch (\Throwable $e) {
                // ignore if FK cannot be added in current DB engine/state
            }
            try {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }

    public function down(): void
    {
        Schema::table('food_groups', function (Blueprint $table) {
            // Safely drop FKs if they exist
            try { $table->dropForeign(['clinic_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['created_by']); } catch (\Throwable $e) {}

            if (Schema::hasColumn('food_groups', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('food_groups', 'clinic_id')) {
                $table->dropColumn('clinic_id');
            }
        });
    }
};

