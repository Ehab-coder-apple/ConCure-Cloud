<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vaccination_schedules')) {
            Schema::create('vaccination_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
                $table->string('name'); // e.g. "Iraq EPI 2025"
                $table->string('version', 20)->default('1.0');
                $table->boolean('is_default')->default(false);
                $table->date('effective_from')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['country_id', 'is_default', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccination_schedules');
    }
};

