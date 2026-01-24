<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This table stores the master list of dental procedures (like a catalog)
     */
    public function up(): void
    {
        Schema::create('dental_procedures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable(); // CDT code (e.g., D0120, D1110)
            $table->text('description')->nullable();
            $table->enum('category', [
                'diagnostic',
                'preventive',
                'restorative',
                'endodontics',
                'periodontics',
                'prosthodontics',
                'oral_surgery',
                'orthodontics',
                'implants',
                'cosmetic',
                'emergency',
                'other'
            ])->default('other');
            $table->decimal('default_cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->integer('estimated_duration_minutes')->nullable();
            $table->text('pre_procedure_instructions')->nullable();
            $table->text('post_procedure_instructions')->nullable();
            $table->boolean('requires_anesthesia')->default(false);
            $table->boolean('is_frequent')->default(false); // For quick access
            $table->unsignedBigInteger('clinic_id')->nullable(); // Null = global, otherwise clinic-specific
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');

            // Indexes
            $table->index(['category', 'is_active']);
            $table->index(['is_frequent', 'is_active']);
            $table->index('clinic_id');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_procedures');
    }
};

