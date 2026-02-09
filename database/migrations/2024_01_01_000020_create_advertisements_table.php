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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->string('title');
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->string('link_url')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('expires_at'); // Calculated from end_date
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->enum('position', ['header', 'sidebar', 'footer', 'popup'])->default('sidebar');
            $table->json('target_roles')->nullable(); // Which user roles can see this ad
            $table->integer('click_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['clinic_id', 'is_active', 'expires_at']);
            $table->index(['start_date', 'end_date']);
            $table->index(['position', 'display_order']);
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
