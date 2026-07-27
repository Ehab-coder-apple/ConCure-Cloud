<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgical_operations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surgical_case_id')->constrained('surgical_cases')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            $table->dateTime('operation_date')->nullable();
            $table->string('theatre')->nullable();
            $table->string('asa_class', 10)->nullable();
            $table->string('anesthesia_type', 50)->nullable();

            // Clinical content
            $table->json('preop_assessment')->nullable();
            $table->longText('operative_note')->nullable();
            $table->json('postop_assessment')->nullable();

            $table->text('complications')->nullable();
            $table->unsignedInteger('estimated_blood_loss_ml')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['surgical_case_id', 'clinic_id']);
            $table->index('operation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgical_operations');
    }
};
