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
        Schema::table('external_labs', function (Blueprint $table) {
            // Dental-specific fields
            $table->json('dental_specialties')->nullable()->after('notes');
            $table->integer('turnaround_days')->nullable()->after('dental_specialties');
            $table->boolean('accepts_digital_impressions')->default(false)->after('turnaround_days');
            $table->text('equipment_capabilities')->nullable()->after('accepts_digital_impressions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_labs', function (Blueprint $table) {
            $table->dropColumn([
                'dental_specialties',
                'turnaround_days',
                'accepts_digital_impressions',
                'equipment_capabilities'
            ]);
        });
    }
};

