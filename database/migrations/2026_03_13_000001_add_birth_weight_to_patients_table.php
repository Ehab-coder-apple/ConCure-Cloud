<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('patients', 'birth_weight')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->decimal('birth_weight', 8, 2)->nullable()->after('weight')
                      ->comment('Birth weight in grams');
                $table->unsignedSmallInteger('gestational_age_weeks')->nullable()->after('birth_weight')
                      ->comment('Gestational age at birth in weeks (for preterm correction)');
            });
        }
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['birth_weight', 'gestational_age_weeks']);
        });
    }
};

