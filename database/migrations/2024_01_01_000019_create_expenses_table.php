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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->unsignedBigInteger('clinic_id');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->enum('category', ['salary', 'rent', 'utilities', 'equipment', 'supplies', 'marketing', 'insurance', 'taxes', 'maintenance', 'other']);
            $table->date('expense_date');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'check', 'other']);
            $table->string('vendor_name')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('receipt_file')->nullable(); // File path
            $table->text('notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_frequency', ['monthly', 'quarterly', 'yearly'])->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            
            $table->index(['clinic_id', 'expense_date']);
            $table->index(['category', 'status']);
            $table->index('expense_number');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
