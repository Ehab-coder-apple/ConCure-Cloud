<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Line-level detail for an invoice return: which original sold item
     * (medicine_transaction), how much of it came back, at what unit price,
     * and (optionally) a line-specific reason override.
     */
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_return_id')->constrained('invoice_returns')->cascadeOnDelete();

            // The original sold line this return applies against. Nullable
            // so a return record survives if the underlying sale line is
            // ever hard-deleted (kept for audit continuity).
            $table->foreignId('medicine_transaction_id')->nullable()
                ->constrained('medicine_transactions')->nullOnDelete();

            $table->foreignId('medicine_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity_returned', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 12, 2);

            // Optional per-line override of the batch reason_code.
            $table->string('reason_code', 50)->nullable();

            $table->timestamps();

            $table->index('medicine_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
