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
        Schema::create('prescription_medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_id');
            $table->unsignedBigInteger('medicine_id')->nullable(); // Null for custom medicines
            $table->string('medicine_name'); // For custom medicines or override
            $table->string('dosage');
            $table->string('frequency'); // e.g., "3 times daily"
            $table->string('duration'); // e.g., "7 days"
            $table->text('instructions')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamps();
            
            $table->index('prescription_id');
            $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('cascade');
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_medicines');
    }
};
