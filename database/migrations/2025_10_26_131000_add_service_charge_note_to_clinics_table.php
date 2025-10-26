<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'service_charge_note')) {
                $table->string('service_charge_note', 500)->nullable()->after('service_charge_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (Schema::hasColumn('clinics', 'service_charge_note')) {
                $table->dropColumn('service_charge_note');
            }
        });
    }
};

