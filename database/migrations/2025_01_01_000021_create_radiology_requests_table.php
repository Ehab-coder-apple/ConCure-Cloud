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
        Schema::create('radiology_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->text('clinical_notes')->nullable();
            $table->text('clinical_history')->nullable();
            $table->text('suspected_diagnosis')->nullable();
            $table->date('requested_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['normal', 'urgent', 'stat'])->default('normal');
            
            // Radiology center information
            $table->string('radiology_center_name')->nullable();
            $table->string('radiology_center_phone')->nullable();
            $table->string('radiology_center_whatsapp')->nullable();
            $table->string('radiology_center_email')->nullable();
            $table->text('radiology_center_address')->nullable();
            
            // Communication tracking
            $table->enum('communication_method', ['whatsapp', 'email', 'phone', 'in_person'])->nullable();
            $table->text('communication_notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            
            // Results tracking
            $table->string('result_file_path')->nullable();
            $table->text('radiologist_report')->nullable();
            $table->text('findings')->nullable();
            $table->text('impression')->nullable();
            $table->timestamp('result_received_at')->nullable();
            $table->unsignedBigInteger('result_received_by')->nullable();
            
            // Additional fields
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // For storing additional structured data
            
            $table->timestamps();

            $table->index(['patient_id', 'requested_date']);
            $table->index('request_number');
            $table->index(['status', 'priority']);
            $table->index('doctor_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('result_received_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_requests');
    }
};
