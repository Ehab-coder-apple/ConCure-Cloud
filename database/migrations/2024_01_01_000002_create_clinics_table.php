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
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->json('settings')->nullable(); // Clinic-specific settings
            $table->boolean('is_active')->default(true);
            $table->integer('max_users')->default(10); // User limit per clinic
            $table->string('activation_code')->unique();
            $table->timestamp('activated_at')->nullable();

            $table->timestamps();
            
            $table->index('is_active');
            $table->index('activation_code');
        });
        
        // Add foreign key to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
        });
        
        Schema::dropIfExists('clinics');
    }
};
