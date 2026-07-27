<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Pivot table allowing a single aesthetic session (direct treatment mode)
     * to have multiple treatments selected. Mirrors the existing
     * aesthetic_package_treatment pattern used by AestheticPackage.
     */
    public function up(): void
    {
        Schema::create('aesthetic_session_treatment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('aesthetic_sessions')->onDelete('cascade');
            $table->foreignId('treatment_id')->constrained('aesthetic_treatments')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['session_id', 'treatment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aesthetic_session_treatment');
    }
};
