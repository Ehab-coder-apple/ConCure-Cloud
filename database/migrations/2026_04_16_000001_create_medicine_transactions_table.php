<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Track medicine sales and purchases for inventory management.
     */
    public function up(): void
    {
        Schema::create('medicine_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->comment('User who performed the transaction');
            
            // Transaction type: 'sale' or 'purchase'
            $table->enum('type', ['sale', 'purchase']);
            
            // Quantity
            $table->integer('quantity')->comment('Quantity sold or purchased');
            
            // Financial details
            $table->decimal('unit_price', 10, 2)->comment('Price per unit at time of transaction');
            $table->decimal('total_amount', 10, 2)->comment('Total transaction amount');
            
            // Transaction details
            $table->string('reference_number', 100)->nullable()->comment('Invoice/Receipt number');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null')->comment('Patient for sales');
            $table->string('supplier_name', 255)->nullable()->comment('Supplier for purchases');
            $table->string('payment_method', 50)->nullable()->comment('cash, card, credit, etc.');
            
            // Stock tracking
            $table->integer('stock_before')->comment('Stock quantity before transaction');
            $table->integer('stock_after')->comment('Stock quantity after transaction');
            
            // Additional info
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            
            $table->timestamps();
            
            // Indexes
            $table->index('transaction_date');
            $table->index('type');
            $table->index(['clinic_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_transactions');
    }
};
