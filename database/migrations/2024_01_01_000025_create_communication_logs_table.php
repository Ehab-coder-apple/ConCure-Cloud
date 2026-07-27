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
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('clinic_id');
            $table->enum('type', ['sms', 'whatsapp', 'email']);
            $table->string('recipient'); // Phone number or email
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('attachment_path')->nullable(); // For PDF files
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed']);
            $table->text('error_message')->nullable();
            $table->string('external_id')->nullable(); // ID from SMS/WhatsApp service
            $table->json('metadata')->nullable(); // Additional data from service
            $table->unsignedBigInteger('sent_by');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['patient_id', 'type']);
            $table->index(['clinic_id', 'sent_at']);
            $table->index(['status', 'type']);
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('sent_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
