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
        Schema::table('surgical_visits', function (Blueprint $table) {
            $table->json('wound_assessment')->nullable()->after('wound_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surgical_visits', function (Blueprint $table) {
            $table->dropColumn('wound_assessment');
        });
    }
};
