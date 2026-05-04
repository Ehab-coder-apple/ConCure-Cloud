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
        Schema::create('patient_packages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('package_id')->constrained('aesthetic_packages')->onDelete('cascade');
            $table->unsignedInteger('sessions_used')->default(0);
            $table->unsignedInteger('sessions_remaining');
            $table->date('purchase_date');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'patient_id']);
            $table->index(['tenant_id', 'package_id']);
            $table->index(['tenant_id', 'purchase_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_packages');
    }
};
