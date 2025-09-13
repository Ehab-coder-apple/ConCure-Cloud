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
        Schema::create('radiology_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->enum('category', [
                'x_ray',
                'ct_scan',
                'mri',
                'ultrasound',
                'mammography',
                'nuclear_medicine',
                'fluoroscopy',
                'angiography',
                'pet_scan',
                'bone_scan',
                'other'
            ])->default('x_ray');
            $table->enum('body_part', [
                'head',
                'neck',
                'chest',
                'abdomen',
                'pelvis',
                'spine',
                'upper_extremity',
                'lower_extremity',
                'whole_body',
                'other'
            ])->nullable();
            $table->text('preparation_instructions')->nullable();
            $table->text('contrast_requirements')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->boolean('requires_contrast')->default(false);
            $table->boolean('requires_fasting')->default(false);
            $table->boolean('is_frequent')->default(false);
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'body_part']);
            $table->index(['is_frequent', 'is_active']);
            $table->index('clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_tests');
    }
};
