<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgical_cases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            $table->string('case_number')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');

            $table->foreignId('primary_surgeon_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assistant_surgeon_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('anesthetist_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('diagnosis')->nullable();
            $table->text('planned_procedure')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['clinic_id', 'patient_id']);
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgical_cases');
    }
};
