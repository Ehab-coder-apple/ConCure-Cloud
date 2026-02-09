<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing records to have 0 for paid_amount if null
        DB::table('dental_treatments')
            ->whereNull('paid_amount')
            ->update(['paid_amount' => 0]);

        Schema::table('dental_treatments', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dental_treatments', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->default(0)->change();
        });
    }
};

