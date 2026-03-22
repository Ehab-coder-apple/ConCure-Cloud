<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('patient_vaccinations')) {
            Schema::create('patient_vaccinations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
                $table->foreignId('vaccine_id')->constrained('vaccines')->cascadeOnDelete();
                $table->unsignedTinyInteger('dose_number')->default(1);
                $table->date('scheduled_date');
                $table->date('given_date')->nullable();
                $table->string('status', 20)->default('upcoming'); // on_time, delayed, missed, upcoming, skipped
                $table->integer('delay_days')->default(0);
                $table->string('batch_number')->nullable();
                $table->text('notes')->nullable();
                $table->string('administered_by')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['patient_id', 'vaccine_id', 'dose_number']);
                $table->index(['patient_id', 'status']);
                $table->index(['scheduled_date', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_vaccinations');
    }
};

