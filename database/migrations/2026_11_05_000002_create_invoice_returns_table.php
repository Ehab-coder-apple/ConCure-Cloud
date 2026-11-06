<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per return event (a cashier/pharmacist submitting the Return
     * Items modal for an invoice). Line-level detail lives in return_items.
     */
    public function up(): void
    {
        Schema::create('invoice_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('medicine_sale_invoices')->cascadeOnDelete();

            // Audit: who processed the return and when.
            $table->foreignId('returned_by_user_id')->constrained('users')->restrictOnDelete();

            // Reason for the whole return batch (see InvoiceReturn::REASON_CODES).
            $table->string('reason_code', 50);
            $table->text('notes')->nullable();

            $table->decimal('refund_amount', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['clinic_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_returns');
    }
};
