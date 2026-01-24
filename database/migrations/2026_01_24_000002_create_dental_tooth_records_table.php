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
        Schema::create('dental_tooth_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dental_chart_id');
            $table->string('tooth_number', 10); // FDI notation: 11-48 for adult, 51-85 for pediatric
            $table->json('conditions')->nullable(); // Array of conditions: caries, filling, crown, etc.
            $table->json('surfaces_affected')->nullable(); // Array: O, M, D, B/F, L/P
            $table->text('notes')->nullable();
            $table->enum('primary_condition', [
                'healthy',
                'caries',
                'filling',
                'crown',
                'root_canal',
                'extraction',
                'implant',
                'bridge',
                'fracture',
                'periodontal',
                'other'
            ])->default('healthy');
            $table->enum('severity', ['mild', 'moderate', 'severe'])->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('dental_chart_id')->references('id')->on('dental_charts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['dental_chart_id', 'tooth_number']);
            $table->index('primary_condition');
            $table->unique(['dental_chart_id', 'tooth_number']); // One record per tooth per chart
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_tooth_records');
    }
};

