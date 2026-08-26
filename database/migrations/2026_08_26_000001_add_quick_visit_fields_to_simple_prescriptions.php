<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Minimal, additive schema support for the "Quick Visit" one-page
     * workflow: a Visit Type on the prescription, and optional Type
     * (dosage form) / Quantity per prescribed medicine. All columns are
     * nullable so the existing (non-quick-visit) prescription create/edit
     * flow is completely unaffected.
     */
    public function up(): void
    {
        Schema::table('simple_prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('simple_prescriptions', 'visit_type')) {
                $table->string('visit_type', 30)->nullable()->after('diagnosis');
            }
        });

        Schema::table('simple_prescription_medicines', function (Blueprint $table) {
            if (!Schema::hasColumn('simple_prescription_medicines', 'type')) {
                $table->string('type', 50)->nullable()->after('medicine_name');
            }
            if (!Schema::hasColumn('simple_prescription_medicines', 'quantity')) {
                $table->unsignedInteger('quantity')->nullable()->after('duration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simple_prescription_medicines', function (Blueprint $table) {
            if (Schema::hasColumn('simple_prescription_medicines', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('simple_prescription_medicines', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('simple_prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('simple_prescriptions', 'visit_type')) {
                $table->dropColumn('visit_type');
            }
        });
    }
};
