<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Standard canal definitions per tooth type (FDI notation).
     */
    public function up(): void
    {
        Schema::create('tooth_canals', function (Blueprint $table) {
            $table->id();
            $table->string('tooth_number', 10); // FDI notation: 11-48
            $table->string('canal_name', 50);   // e.g., MB1, MB2, DB, P, ML, DL
            $table->string('canal_code', 20);   // Short code for programmatic use
            $table->string('tooth_type', 30);    // incisor, canine, premolar, molar
            $table->string('arch', 10);          // upper, lower
            $table->integer('display_order')->default(0);
            $table->boolean('is_common')->default(true); // Common vs rare canal (e.g., MB2)
            $table->timestamps();

            $table->index('tooth_number');
            $table->index('tooth_type');
            $table->unique(['tooth_number', 'canal_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tooth_canals');
    }
};

