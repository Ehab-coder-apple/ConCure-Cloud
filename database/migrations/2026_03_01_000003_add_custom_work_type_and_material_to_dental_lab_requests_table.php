<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_lab_requests', function (Blueprint $table) {
            $table->string('work_type')->nullable()->change();
        });

        if (!Schema::hasColumn('dental_lab_requests', 'custom_work_type')) {
            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->string('custom_work_type')->nullable()->after('work_type');
            });
        }

        if (!Schema::hasColumn('dental_lab_requests', 'custom_material')) {
            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->string('custom_material')->nullable()->after('material');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dental_lab_requests', 'custom_work_type')) {
            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->dropColumn('custom_work_type');
            });
        }

        if (Schema::hasColumn('dental_lab_requests', 'custom_material')) {
            Schema::table('dental_lab_requests', function (Blueprint $table) {
                $table->dropColumn('custom_material');
            });
        }
    }
};

