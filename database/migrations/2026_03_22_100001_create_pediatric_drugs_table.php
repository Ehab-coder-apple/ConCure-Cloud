<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pediatric_drugs', function (Blueprint $table) {
            $table->id();
            $table->string('generic_name');
            $table->string('brand_name')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('generic_name');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pediatric_drugs');
    }
};

