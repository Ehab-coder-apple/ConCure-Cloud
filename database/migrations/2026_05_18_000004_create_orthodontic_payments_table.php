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
        Schema::create('orthodontic_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orthodontic_case_id')->constrained('orthodontic_cases')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('payment_method'); // cash, card, bank_transfer, insurance
            $table->string('payment_type')->default('installment'); // deposit, installment, balance, adjustment
            $table->integer('installment_number')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('received_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['orthodontic_case_id', 'payment_date']);
            $table->index(['clinic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orthodontic_payments');
    }
};
