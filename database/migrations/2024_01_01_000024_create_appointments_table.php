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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('clinic_id');
            $table->datetime('appointment_datetime');
            $table->integer('duration_minutes')->default(30);
            $table->enum('type', ['consultation', 'follow_up', 'checkup', 'procedure', 'other']);
            $table->enum('status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->boolean('send_reminder')->default(true);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['clinic_id', 'appointment_datetime']);
            $table->index(['patient_id', 'appointment_datetime']);
            $table->index(['doctor_id', 'appointment_datetime']);
            $table->index(['status', 'appointment_datetime']);
            $table->index('appointment_number');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
