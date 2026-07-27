<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedule_items')) {
            Schema::create('schedule_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('schedule_id')->constrained('vaccination_schedules')->cascadeOnDelete();
                $table->foreignId('vaccine_id')->constrained('vaccines')->cascadeOnDelete();
                $table->unsignedTinyInteger('dose_number')->default(1);
                $table->unsignedSmallInteger('recommended_age_value');
                $table->string('recommended_age_unit', 10)->default('months'); // days, weeks, months, years
                $table->unsignedSmallInteger('min_age_value')->nullable();
                $table->unsignedSmallInteger('max_age_value')->nullable();
                $table->unsignedSmallInteger('grace_period_days')->default(7);
                $table->boolean('is_mandatory')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['schedule_id', 'vaccine_id', 'dose_number']);
                $table->index(['schedule_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_items');
    }
};

