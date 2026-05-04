<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_aesthetic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained('patients')->cascadeOnDelete();
            $table->string('skin_type', 30)->nullable();
            $table->json('skin_concerns')->nullable();
            $table->text('allergies')->nullable();
            $table->text('previous_treatments')->nullable();
            $table->text('current_skincare_routine')->nullable();
            $table->text('desired_outcomes')->nullable();
            $table->string('sun_exposure', 20)->nullable();
            $table->boolean('is_pregnant_or_breastfeeding')->default(false);
            $table->boolean('photosensitivity')->default(false);
            $table->text('medical_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_aesthetic');
    }
};
