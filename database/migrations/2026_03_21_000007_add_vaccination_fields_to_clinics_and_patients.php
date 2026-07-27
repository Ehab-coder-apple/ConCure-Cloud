<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add country_id and schedule_override_id to clinics
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'country_id')) {
                $table->foreignId('country_id')->nullable()->after('id')->constrained('countries')->nullOnDelete();
            }
            if (!Schema::hasColumn('clinics', 'schedule_override_id')) {
                $table->foreignId('schedule_override_id')->nullable()->after('country_id')->constrained('vaccination_schedules')->nullOnDelete();
            }
        });

        // Add vaccination_schedule_id to patients
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'vaccination_schedule_id')) {
                $table->foreignId('vaccination_schedule_id')->nullable()->after('clinic_id')->constrained('vaccination_schedules')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'vaccination_schedule_id')) {
                $table->dropForeign(['vaccination_schedule_id']);
                $table->dropColumn('vaccination_schedule_id');
            }
        });

        Schema::table('clinics', function (Blueprint $table) {
            $toDrop = [];
            if (Schema::hasColumn('clinics', 'schedule_override_id')) {
                $table->dropForeign(['schedule_override_id']);
                $toDrop[] = 'schedule_override_id';
            }
            if (Schema::hasColumn('clinics', 'country_id')) {
                $table->dropForeign(['country_id']);
                $toDrop[] = 'country_id';
            }
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};

