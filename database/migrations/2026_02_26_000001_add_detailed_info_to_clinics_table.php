<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'speciality')) {
                $table->string('speciality')->nullable();
            }
            if (!Schema::hasColumn('clinics', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('clinics', 'area')) {
                $table->string('area')->nullable();
            }
            if (!Schema::hasColumn('clinics', 'street')) {
                $table->string('street')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $toDrop = [];
            foreach (['speciality', 'city', 'area', 'street'] as $col) {
                if (Schema::hasColumn('clinics', $col)) {
                    $toDrop[] = $col;
                }
            }

            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
