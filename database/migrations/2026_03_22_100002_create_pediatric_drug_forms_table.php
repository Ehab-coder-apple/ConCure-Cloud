<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pediatric_drug_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')->constrained('pediatric_drugs')->cascadeOnDelete();
            $table->string('form'); // syrup, tablet, suppository, injection, drops
            $table->string('concentration'); // e.g. "120mg/5ml", "250mg/tablet"
            $table->decimal('concentration_mg', 10, 2); // numeric mg part
            $table->decimal('concentration_per_ml', 10, 2)->nullable(); // ml part (for syrups)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('drug_id');
            $table->index('form');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pediatric_drug_forms');
    }
};

