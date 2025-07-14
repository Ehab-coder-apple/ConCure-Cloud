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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->enum('role', ['program_owner', 'admin', 'doctor', 'assistant', 'nurse', 'accountant', 'patient']);
            $table->boolean('is_active')->default(true);
            $table->string('activation_code')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->string('language', 2)->default('en');
            $table->json('permissions')->nullable(); // Additional role-specific permissions
            $table->unsignedBigInteger('clinic_id')->nullable(); // For multi-clinic support
            $table->unsignedBigInteger('created_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['role', 'is_active']);
            $table->index('clinic_id');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
