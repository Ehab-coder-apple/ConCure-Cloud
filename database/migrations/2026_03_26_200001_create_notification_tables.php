<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Notification Settings — clinic-level preferences & templates
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->boolean('appointment_reminder_enabled')->default(false);
            $table->integer('appointment_reminder_hours')->default(24); // hours before appointment
            $table->text('appointment_reminder_template')->nullable();
            $table->boolean('vaccination_reminder_enabled')->default(false);
            $table->integer('vaccination_reminder_days')->default(3); // days before due date
            $table->text('vaccination_reminder_template')->nullable();
            $table->boolean('follow_up_reminder_enabled')->default(false);
            $table->integer('follow_up_reminder_days')->default(1); // days before follow-up
            $table->text('follow_up_reminder_template')->nullable();
            $table->boolean('whatsapp_enabled')->default(false);
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->unique('clinic_id'); // one settings row per clinic
            $table->index('clinic_id');
        });

        // 2. Notification Logs — delivery tracking
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('type', 50); // appointment_reminder, vaccination_reminder, follow_up_reminder
            $table->string('channel', 20)->default('whatsapp'); // whatsapp, sms, email
            $table->string('recipient', 50)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending'); // pending, sent, delivered, failed
            $table->text('error_message')->nullable();
            $table->string('external_id', 255)->nullable(); // provider message ID
            $table->nullableMorphs('notifiable'); // polymorphic: Appointment, PatientVaccination, etc.
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->index('clinic_id');
            $table->index(['clinic_id', 'type']);
            $table->index(['clinic_id', 'status']);
            $table->index('sent_at');
        });

        // 3. Scheduled Notifications — queue for future reminders
        Schema::create('scheduled_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id');
            $table->string('type', 50); // appointment_reminder, vaccination_reminder, follow_up_reminder
            $table->string('channel', 20)->default('whatsapp');
            $table->timestamp('scheduled_at');
            $table->string('status', 20)->default('pending'); // pending, processing, sent, failed, cancelled
            $table->nullableMorphs('notifiable'); // polymorphic: Appointment, PatientVaccination, etc.
            $table->json('payload')->nullable(); // pre-computed template data
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->index('clinic_id');
            $table->index(['status', 'scheduled_at']); // main query index
            $table->index(['clinic_id', 'status']);
            // notifiable index already created by nullableMorphs()
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_notifications');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_settings');
    }
};

