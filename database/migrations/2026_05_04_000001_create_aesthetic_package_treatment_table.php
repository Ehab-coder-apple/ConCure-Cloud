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
        Schema::create('aesthetic_package_treatment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('aesthetic_packages')->onDelete('cascade');
            $table->foreignId('treatment_id')->constrained('aesthetic_treatments')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['package_id', 'treatment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aesthetic_package_treatment');
    }
};
