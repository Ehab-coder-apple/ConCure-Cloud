<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add dispensing tracking to simple_prescriptions table.
     */
    public function up(): void
    {
        Schema::table('simple_prescriptions', function (Blueprint $table) {
            $table->boolean('is_dispensed')->default(false)->after('status');
            $table->timestamp('dispensed_at')->nullable()->after('is_dispensed');
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->onDelete('set null')->after('dispensed_at');
            $table->string('dispense_reference', 100)->nullable()->after('dispensed_by')->comment('Reference number for the bulk sale transaction');
            
            $table->index('is_dispensed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simple_prescriptions', function (Blueprint $table) {
            $table->dropForeign(['dispensed_by']);
            $table->dropIndex(['is_dispensed']);
            $table->dropColumn([
                'is_dispensed',
                'dispensed_at',
                'dispensed_by',
                'dispense_reference',
            ]);
        });
    }
};
