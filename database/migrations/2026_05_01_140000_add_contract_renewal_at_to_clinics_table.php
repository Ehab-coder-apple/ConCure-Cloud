<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'contract_renewal_at')) {
                $table->date('contract_renewal_at')->nullable()->after('service_charge_note');
                $table->index('contract_renewal_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (Schema::hasColumn('clinics', 'contract_renewal_at')) {
                try { $table->dropIndex(['contract_renewal_at']); } catch (\Throwable $e) {}
                $table->dropColumn('contract_renewal_at');
            }
        });
    }
};
