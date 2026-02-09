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
            $table->enum('lab_type', ['medical', 'dental'])->default('medical')->after('name');
            $table->index(['clinic_id', 'lab_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_labs', function (Blueprint $table) {
            $table->dropIndex(['clinic_id', 'lab_type', 'is_active']);
            $table->dropColumn('lab_type');
        });
    }
};

