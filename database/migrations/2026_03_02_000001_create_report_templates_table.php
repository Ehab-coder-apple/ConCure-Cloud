<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('report_templates')) {
            Schema::create('report_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->string('name');
                $table->string('title')->nullable();
                $table->text('content');
                $table->string('icon')->nullable()->default('fas fa-file-alt');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['clinic_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};

