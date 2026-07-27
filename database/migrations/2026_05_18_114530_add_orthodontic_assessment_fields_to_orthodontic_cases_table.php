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
            // Clinical Assessment Fields
            $table->string('skeletal_class')->nullable()->after('malocclusion_class');
            $table->decimal('overjet', 5, 2)->nullable()->after('skeletal_class')->comment('Measurement in mm');
            $table->decimal('overbite', 5, 2)->nullable()->after('overjet')->comment('Measurement in mm or percentage');
            $table->string('midline')->nullable()->after('overbite');
            $table->string('crowding')->nullable()->after('midline');
            $table->string('crossbite')->nullable()->after('crowding');
            $table->decimal('open_bite', 5, 2)->nullable()->after('crossbite')->comment('Measurement in mm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orthodontic_cases', function (Blueprint $table) {
            $table->dropColumn([
                'skeletal_class',
                'overjet',
                'overbite',
                'midline',
                'crowding',
                'crossbite',
                'open_bite',
            ]);
        });
    }
};
