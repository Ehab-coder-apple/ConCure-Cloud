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
        Schema::table('orthodontic_cases', function (Blueprint $table) {
            // Visual Tooth Chart - JSON column to store tooth states
            // Keys: tooth numbers (1-32 Universal Numbering System)
            // Values: orthodontic status (bracket_placed, missing_bracket, band, elastic_attachment, extraction_space)
            $table->json('tooth_states')->nullable()->after('open_bite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orthodontic_cases', function (Blueprint $table) {
            $table->dropColumn('tooth_states');
        });
    }
};
