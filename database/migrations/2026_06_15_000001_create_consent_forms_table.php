<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_forms', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->index();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('aesthetic_sessions')->nullOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained('aesthetic_treatments')->nullOnDelete();
            $table->foreignId('patient_file_id')->nullable()->constrained('patient_files')->nullOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->longText('signature_data');
            $table->timestamp('signed_at');
            $table->string('signer_name')->nullable();
            $table->string('pdf_file_name')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedBigInteger('pdf_file_size')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'patient_id']);
            $table->index(['tenant_id', 'session_id']);
            $table->index(['tenant_id', 'signed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_forms');
    }
};