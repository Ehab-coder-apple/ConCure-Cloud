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
        Schema::table('transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('transfers', 'priority')) {
                $table->string('priority', 16)->default('normal')->after('status');
                $table->index(['clinic_id', 'priority']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            if (Schema::hasColumn('transfers', 'priority')) {
                $table->dropIndex(['clinic_id', 'priority']);
                $table->dropColumn('priority');
            }
        });
    }
};
