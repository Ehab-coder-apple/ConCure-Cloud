<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Expand billing_cycle enum to include quarterly and semiannual
        DB::statement("ALTER TABLE clinics MODIFY billing_cycle ENUM('monthly','quarterly','semiannual','yearly') NOT NULL DEFAULT 'monthly'");
    }

    public function down(): void
    {
        // Revert back to monthly/yearly only
        DB::statement("ALTER TABLE clinics MODIFY billing_cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly'");
    }
};

