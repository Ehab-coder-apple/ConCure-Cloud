<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Be extra defensive: add column only if missing, and ignore duplicate errors just in case
        if (!Schema::hasColumn('prescriptions', 'clinic_id')) {
            try {
                Schema::table('prescriptions', function (Blueprint $table) {
                    // Add the column first
                    $table->unsignedBigInteger('clinic_id')->after('doctor_id');
                });
            } catch (\Throwable $e) {
                // If column already exists in prod but Schema::hasColumn returned false for any reason
                if (stripos($e->getMessage(), 'Duplicate column name') === false) {
                    throw $e;
                }
            }
        }
        // Then add the foreign key in a separate statement (and ignore if it already exists)
        try {
            Schema::table('prescriptions', function (Blueprint $table) {
                // Attempt to add FK; if it already exists this will throw and be ignored
                $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // Ignore if foreign key already exists or the DB engine doesn't support it at this stage
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('prescriptions', 'clinic_id')) {
            try {
                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->dropForeign(['clinic_id']);
                });
            } catch (\Throwable $e) {
                // Foreign key might not exist
            }
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropColumn('clinic_id');
            });
        }
    }
};
