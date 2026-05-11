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
        if (!Schema::hasColumn('clinic_contracts', 'annual_fee')) {
            Schema::table('clinic_contracts', function (Blueprint $table) {
                $table->decimal('annual_fee', 10, 2)->nullable()->after('contract_content');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('clinic_contracts', 'annual_fee')) {
            Schema::table('clinic_contracts', function (Blueprint $table) {
                $table->dropColumn('annual_fee');
            });
        }
    }
};
