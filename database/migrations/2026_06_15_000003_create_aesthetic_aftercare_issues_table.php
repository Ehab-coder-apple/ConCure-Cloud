<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aesthetic_aftercare_issues', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->index();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('aesthetic_sessions')->nullOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained('aesthetic_treatments')->nullOnDelete();
            $table->foreignId('aftercare_template_id')->nullable()->constrained('aesthetic_aftercare_templates')->nullOnDelete();
            $table->foreignId('patient_file_id')->nullable()->constrained('patient_files')->nullOnDelete();
            $table->string('template_name');
            $table->string('template_category', 100)->nullable();
            $table->string('title');
            $table->longText('instructions_snapshot');
            $table->text('notes')->nullable();
            $table->timestamp('issued_at');
            $table->string('pdf_file_name')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedBigInteger('pdf_file_size')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'patient_id']);
            $table->index(['tenant_id', 'session_id']);
            $table->index(['tenant_id', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aesthetic_aftercare_issues');
    }
};