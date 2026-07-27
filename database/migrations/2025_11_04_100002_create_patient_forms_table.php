<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patient_forms', function (Blueprint $table) {
            $table->id();

            // Ownership
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('form_template_id')->constrained('form_templates')->cascadeOnDelete();

            // Assignment & completion trail
            $table->foreignId('assigned_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('filled_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('completed_at')->nullable();

            // Status & data
            $table->enum('status', ['assigned', 'in_progress', 'completed'])->default('assigned')->index();
            $table->longText('form_data')->nullable(); // JSON payload captured when completing (we will encode/decode at app level)
            $table->text('notes')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index('clinic_id');
            $table->index('patient_id');
            $table->index('form_template_id');
            $table->index(['clinic_id', 'patient_id']);
            $table->index(['clinic_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_forms');
    }
};

