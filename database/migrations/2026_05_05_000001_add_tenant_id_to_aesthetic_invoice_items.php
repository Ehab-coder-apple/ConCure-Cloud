<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('aesthetic_invoice_items', 'tenant_id')) {
            Schema::table('aesthetic_invoice_items', function (Blueprint $table) {
                $table->string('tenant_id', 20)->nullable()->after('invoice_id');
                $table->index('tenant_id');
            });

            // Backfill from parent invoices
            $driver = config('database.default');
            if ($driver === 'mysql') {
                DB::statement('
                    UPDATE aesthetic_invoice_items AS ii
                    JOIN aesthetic_invoices AS i ON ii.invoice_id = i.id
                    SET ii.tenant_id = i.tenant_id
                    WHERE ii.tenant_id IS NULL
                ');
            }
        }
    }

    public function down(): void
    {
        Schema::table('aesthetic_invoice_items', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
