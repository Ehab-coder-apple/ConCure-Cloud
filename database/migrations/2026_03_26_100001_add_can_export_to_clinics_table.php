<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'can_export')) {
                $table->boolean('can_export')->default(true)->after('is_demo');
            }
        });

        // Demo clinics default to can_export = false (require master admin permission)
        DB::table('clinics')
            ->where('is_demo', true)
            ->update(['can_export' => false]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('clinics', 'can_export')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropColumn('can_export');
            });
        }
    }
};

