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
        Schema::create('patient_checkups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('bmi', 4, 2)->nullable();
            $table->string('blood_pressure')->nullable(); // e.g., "120/80"
            $table->integer('heart_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('blood_sugar', 5, 2)->nullable();
            $table->text('symptoms')->nullable();
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->timestamp('checkup_date');
            $table->timestamps();
            
            $table->index(['patient_id', 'checkup_date']);
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_checkups');
    }
};
