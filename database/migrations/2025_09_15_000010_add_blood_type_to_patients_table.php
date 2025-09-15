<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('patients') && !Schema::hasColumn('patients', 'blood_type')) {
            Schema::table('patients', function (Blueprint $table) {
                // Keep it flexible: string up to 3 chars (e.g., "AB+", "NA")
                $table->string('blood_type', 3)->nullable()->after('bmi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('patients') && Schema::hasColumn('patients', 'blood_type')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropColumn('blood_type');
            });
        }
    }
};

