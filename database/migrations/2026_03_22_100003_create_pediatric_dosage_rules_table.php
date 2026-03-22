<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pediatric_dosage_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')->constrained('pediatric_drugs')->cascadeOnDelete();
            $table->decimal('mg_per_kg_min', 8, 2); // minimum mg/kg/dose
            $table->decimal('mg_per_kg_max', 8, 2); // maximum mg/kg/dose
            $table->decimal('max_daily_mg', 10, 2)->nullable(); // absolute max daily mg
            $table->integer('frequency_per_day')->default(3); // times per day
            $table->integer('frequency_hours')->nullable(); // hours between doses
            $table->integer('min_age_months')->nullable();
            $table->integer('max_age_months')->nullable();
            $table->decimal('min_weight_kg', 6, 2)->nullable();
            $table->decimal('max_weight_kg', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('drug_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pediatric_dosage_rules');
    }
};

