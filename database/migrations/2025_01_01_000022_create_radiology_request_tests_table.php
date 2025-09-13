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
        Schema::create('radiology_request_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('radiology_request_id');
            $table->unsignedBigInteger('radiology_test_id')->nullable(); // Reference to predefined test
            $table->string('test_name'); // Custom test name or from database
            $table->text('instructions')->nullable(); // Special instructions for this test
            $table->text('clinical_indication')->nullable(); // Why this test is needed
            $table->boolean('with_contrast')->default(false);
            $table->boolean('urgent')->default(false);
            $table->text('special_requirements')->nullable();
            $table->timestamps();

            $table->index('radiology_request_id');
            $table->index('radiology_test_id');
            $table->foreign('radiology_request_id')->references('id')->on('radiology_requests')->onDelete('cascade');
            $table->foreign('radiology_test_id')->references('id')->on('radiology_tests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_request_tests');
    }
};
