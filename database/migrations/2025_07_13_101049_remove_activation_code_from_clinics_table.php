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
        Schema::table('clinics', function (Blueprint $table) {
            // Remove the activation_code field and its unique constraint
            // The activation codes are properly managed in the activation_codes table
            $table->dropUnique(['activation_code']);
            $table->dropIndex(['activation_code']);
            $table->dropColumn('activation_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Restore the activation_code field if needed for rollback
            $table->string('activation_code')->nullable();
            $table->unique('activation_code');
            $table->index('activation_code');
        });
    }
};
