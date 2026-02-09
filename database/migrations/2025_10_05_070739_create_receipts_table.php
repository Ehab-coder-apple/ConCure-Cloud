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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->enum('category', [
                'consultation_fee',
                'procedure_fee',
                'medication_sale',
                'lab_test_fee',
                'equipment_rental',
                'insurance_reimbursement',
                'donation',
                'refund',
                'other'
            ]);
            $table->date('receipt_date');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'check', 'other']);
            $table->string('payer_name')->nullable();
            $table->string('reference_number')->nullable(); // For invoice reference, check number, etc.
            $table->string('receipt_file')->nullable(); // Scanned receipt/document
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['clinic_id', 'receipt_date']);
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
