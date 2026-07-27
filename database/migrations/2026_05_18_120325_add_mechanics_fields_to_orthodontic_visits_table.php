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
        Schema::table('orthodontic_visits', function (Blueprint $table) {
            // Clinical Mechanics Fields
            $table->string('upper_wire')->nullable()->after('notes')->comment('e.g., 0.14 NiTi, 16x22 SS');
            $table->string('lower_wire')->nullable()->after('upper_wire')->comment('e.g., 0.16 NiTi, 17x25 SS');
            $table->string('elastic_type')->nullable()->after('lower_wire')->comment('e.g., Class II, 1/8" 4oz');
            $table->string('power_chain')->nullable()->after('elastic_type')->comment('e.g., Upper 3-3, Closed');
            $table->string('coil_spring')->nullable()->after('power_chain')->comment('e.g., Open coil 14-15');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orthodontic_visits', function (Blueprint $table) {
            $table->dropColumn([
                'upper_wire',
                'lower_wire',
                'elastic_type',
                'power_chain',
                'coil_spring',
            ]);
        });
    }
};
