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
        Schema::table('users', function (Blueprint $table) {
            // Add font size field for doctor name
            // Default size: 12px (matching current hardcoded size in PDF)
            $table->unsignedTinyInteger('doctor_name_font_size')->default(12)->after('title_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('doctor_name_font_size');
        });
    }
};
