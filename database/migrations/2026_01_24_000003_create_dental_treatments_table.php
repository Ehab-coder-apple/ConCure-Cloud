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
        Schema::create('dental_treatments', function (Blueprint $table) {
            $table->id();
            $table->string('treatment_number')->unique(); // Auto-generated: DT-YYYYMMDD-XXXX
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('dental_chart_id')->nullable(); // Link to specific chart
            $table->string('tooth_number', 10)->nullable(); // Can be null for general treatments
            $table->json('tooth_numbers')->nullable(); // For multi-tooth procedures
            $table->string('procedure_name');
            $table->string('procedure_code')->nullable(); // CDT code or custom code
            $table->text('diagnosis')->nullable();
            $table->string('icd10_code')->nullable(); // Optional ICD-10 dental diagnosis code
            $table->json('surfaces_affected')->nullable(); // For surface-specific procedures
            $table->text('description')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->integer('estimated_duration_minutes')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('severity', ['mild', 'moderate', 'severe'])->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->unsignedBigInteger('assigned_doctor_id');
            $table->unsignedBigInteger('performed_by_id')->nullable(); // Actual doctor who performed
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('post_treatment_notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('dental_chart_id')->references('id')->on('dental_charts')->onDelete('set null');
            $table->foreign('assigned_doctor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('performed_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['patient_id', 'status']);
            $table->index(['clinic_id', 'status']);
            $table->index(['assigned_doctor_id', 'status']);
            $table->index(['scheduled_date', 'status']);
            $table->index('priority');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_treatments');
    }
};

