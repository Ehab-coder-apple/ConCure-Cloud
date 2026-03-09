<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->string('path');              // storage path on Spaces
            $table->string('filename');           // original filename
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('condition_tags')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'patient_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_videos');
    }
};

