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
        Schema::create('session_inventory_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('aesthetic_sessions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('aesthetic_inventory')->onDelete('cascade');
            $table->unsignedInteger('quantity_used');
            $table->timestamps();

            $table->unique(['session_id', 'product_id']);
            $table->index(['session_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_inventory_usage');
    }
};
