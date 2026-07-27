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
        Schema::create('lab_request_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_request_id');
            $table->unsignedBigInteger('lab_test_id')->nullable(); // Null for custom tests
            $table->string('test_name'); // For custom tests or override
            $table->text('instructions')->nullable();
            $table->timestamps();
            
            $table->index('lab_request_id');
            $table->foreign('lab_request_id')->references('id')->on('lab_requests')->onDelete('cascade');
            $table->foreign('lab_test_id')->references('id')->on('lab_tests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_request_tests');
    }
};
