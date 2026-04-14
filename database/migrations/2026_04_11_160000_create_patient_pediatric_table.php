<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_pediatric', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained('patients')->cascadeOnDelete();
            $table->decimal('birth_weight', 8, 2)->nullable();
            $table->unsignedInteger('gestational_age')->nullable();
            $table->string('feeding_type')->nullable();
            $table->string('vaccination_status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_pediatric');
    }
};