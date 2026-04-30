<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Operational expenses tracked at the SaaS-platform level (NOT per-tenant
     * clinic running costs — those live in the `expenses` table). Used by
     * the master finance dashboard to compute Net Profit = Revenue - Expenses.
     *
     * Scope-locked per the product spec:
     *   - IQD only (no currency column)
     *   - No attachments
     *   - No approval workflow
     *   - No recurring entries
     *   - Super-admin only (enforced at the route level via super.admin
     *     middleware on the /master group)
     */
    public function up(): void
    {
        Schema::create('master_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category', 32);
            $table->string('description');
            $table->decimal('amount', 14, 2);
            $table->date('expense_date');
            $table->string('payment_method', 32)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('expense_date');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_expenses');
    }
};
