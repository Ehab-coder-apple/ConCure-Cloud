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
        Schema::create('orthodontic_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');
            
            // Case details
            $table->string('treatment_type'); // metal_braces, ceramic_braces, clear_aligners, lingual_braces
            $table->text('diagnosis')->nullable();
            $table->string('malocclusion_class')->nullable(); // Class I, II, III
            $table->text('treatment_objectives')->nullable();
            
            // Timeline
            $table->date('start_date');
            $table->integer('estimated_duration_months');
            $table->date('estimated_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->string('current_phase')->nullable(); // initial, alignment, space_closure, finishing, retention
            
            // Status
            $table->string('status')->default('active'); // active, paused, completed, cancelled
            
            // Financial
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('payment_plan')->nullable(); // full, monthly, custom
            
            // Appliances
            $table->json('appliances')->nullable(); // List of appliances used
            $table->text('notes')->nullable();
            
            // Tracking
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['clinic_id', 'status']);
            $table->index(['patient_id']);
            $table->index(['doctor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orthodontic_cases');
    }
};
