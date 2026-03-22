<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vaccine_translations')) {
            Schema::create('vaccine_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vaccine_id')->constrained('vaccines')->cascadeOnDelete();
                $table->string('language_code', 10); // en, ar, ku, fr
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['vaccine_id', 'language_code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccine_translations');
    }
};

