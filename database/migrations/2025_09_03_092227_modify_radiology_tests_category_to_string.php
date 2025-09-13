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
        Schema::table('radiology_tests', function (Blueprint $table) {
            // Change category from enum to string to allow custom categories
            $table->string('category', 100)->default('x_ray')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radiology_tests', function (Blueprint $table) {
            // Revert back to enum
            $table->enum('category', [
                'x_ray',
                'ct_scan',
                'mri',
                'ultrasound',
                'mammography',
                'nuclear_medicine',
                'fluoroscopy',
                'angiography',
                'pet_scan',
                'bone_scan',
                'other'
            ])->default('x_ray')->change();
        });
    }
};
