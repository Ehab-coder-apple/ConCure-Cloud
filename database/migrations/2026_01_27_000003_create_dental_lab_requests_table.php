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
        Schema::create('dental_lab_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('dental_treatment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('external_lab_id')->nullable()->constrained()->onDelete('set null');
            
            // Lab work details
            $table->enum('work_type', [
                'crown',
                'bridge',
                'denture_full',
                'denture_partial',
                'implant_crown',
                'implant_bridge',
                'veneer',
                'inlay_onlay',
                'orthodontic_appliance',
                'night_guard',
                'sports_guard',
                'temporary_crown',
                'other'
            ]);
            $table->string('tooth_number')->nullable();
            $table->json('tooth_numbers')->nullable();
            $table->string('shade')->nullable(); // Tooth shade/color
            $table->enum('material', [
                'porcelain',
                'zirconia',
                'emax',
                'metal',
                'pfm', // Porcelain-fused-to-metal
                'acrylic',
                'composite',
                'gold',
                'other'
            ])->nullable();
            $table->text('specifications')->nullable();
            $table->text('special_instructions')->nullable();
            
            // Dates and status
            $table->date('requested_date');
            $table->date('due_date')->nullable();
            $table->date('received_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['normal', 'urgent', 'rush'])->default('normal');
            
            // Communication
            $table->timestamp('sent_at')->nullable();
            $table->enum('communication_method', ['email', 'whatsapp', 'phone', 'manual'])->nullable();
            $table->text('communication_notes')->nullable();
            
            // Cost
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            
            // Files
            $table->string('prescription_file_path')->nullable();
            $table->string('impression_file_path')->nullable(); // Digital impression file
            $table->string('result_file_path')->nullable();
            
            // Tracking
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->text('quality_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['clinic_id', 'status']);
            $table->index(['patient_id', 'requested_date']);
            $table->index('request_number');
            $table->index(['status', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_lab_requests');
    }
};

