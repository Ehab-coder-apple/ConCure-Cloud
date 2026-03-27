<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pediatric_drugs', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('is_active');
            $table->foreignId('clinic_id')->nullable()->after('is_system')->constrained('clinics')->nullOnDelete();
            $table->index('is_system');
            $table->index('clinic_id');
        });

        // Mark all existing drugs as system drugs
        DB::table('pediatric_drugs')->update(['is_system' => true]);
    }

    public function down(): void
    {
        Schema::table('pediatric_drugs', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropIndex(['is_system']);
            $table->dropIndex(['clinic_id']);
            $table->dropColumn(['is_system', 'clinic_id']);
        });
    }
};

