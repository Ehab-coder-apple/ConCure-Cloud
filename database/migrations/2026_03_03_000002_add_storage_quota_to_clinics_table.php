<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'storage_limit')) {
                // Default 5 GB = 5 * 1024 * 1024 * 1024 = 5368709120 bytes
                $table->unsignedBigInteger('storage_limit')->default(5368709120)->after('max_users');
            }
            if (!Schema::hasColumn('clinics', 'storage_used')) {
                $table->unsignedBigInteger('storage_used')->default(0)->after('storage_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $toDrop = [];
            foreach (['storage_limit', 'storage_used'] as $col) {
                if (Schema::hasColumn('clinics', $col)) {
                    $toDrop[] = $col;
                }
            }
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};

