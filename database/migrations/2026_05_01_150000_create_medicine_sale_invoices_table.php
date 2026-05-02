<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->comment('Pharmacist / cashier who issued the sale');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null');

            $table->string('invoice_number', 100)->unique();
            $table->string('payment_method', 50)->nullable();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->dateTime('sold_at');

            $table->timestamps();

            $table->index(['clinic_id', 'sold_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_sale_invoices');
    }
};
