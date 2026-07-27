<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('clinics', 'enabled_modules')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->json('enabled_modules')->nullable()->after('is_demo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('clinics', 'enabled_modules')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropColumn('enabled_modules');
            });
        }
    }
};

