<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a nullable barcode field to medicines so clinic staff can assign
     * a barcode per medicine and scan it (via a USB/Bluetooth hardware
     * scanner) on the Create Sell page to quickly add items to an invoice.
     *
     * The unique index is scoped per clinic so two different clinics may
     * use overlapping barcode values (e.g. generic manufacturer barcodes),
     * while the same clinic cannot assign the same barcode to two
     * different medicines. Rows with a NULL barcode are not constrained by
     * the unique index (standard SQL behavior).
     */
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('barcode', 100)->nullable()->after('batch_number');
            $table->unique(['clinic_id', 'barcode'], 'medicines_clinic_id_barcode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropUnique('medicines_clinic_id_barcode_unique');
            $table->dropColumn('barcode');
        });
    }
};
