<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant-defined medicine forms.
 *
 * The Medicine model exposes a fixed set of canonical forms (Tablet, Capsule,
 * Syrup, etc.) via Medicine::FORMS. Clinics frequently need additional forms
 * that aren't on that list (e.g. Gel, Lozenge, Spray, Gargle). This table
 * holds those clinic-scoped custom forms; they are merged with the built-in
 * list whenever the medicine form dropdown is rendered for that clinic.
 *
 * Scope: per-clinic. A custom form added by clinic A is NOT visible to
 * clinic B.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('medicine_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('label', 100);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['clinic_id', 'key']);
            $table->index('clinic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_forms');
    }
};
