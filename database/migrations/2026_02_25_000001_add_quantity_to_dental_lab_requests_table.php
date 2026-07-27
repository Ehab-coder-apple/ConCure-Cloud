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
        if (Schema::hasColumn('dental_lab_requests', 'quantity')) {
            return;
        }

        Schema::table('dental_lab_requests', function (Blueprint $table) {
            $table->unsignedInteger('quantity')
                ->nullable()
                ->after('tooth_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('dental_lab_requests', 'quantity')) {
            return;
        }

        Schema::table('dental_lab_requests', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
