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
        Schema::create('dental_charts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('visit_id')->nullable(); // Link to appointment/visit if exists
            $table->enum('chart_type', ['adult', 'pediatric'])->default('adult');
            $table->unsignedBigInteger('created_by'); // Doctor who created the chart
            $table->text('general_notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft delete for audit trail

            // Foreign keys
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['patient_id', 'created_at']);
            $table->index(['clinic_id', 'created_at']);
            $table->index('chart_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_charts');
    }
};

