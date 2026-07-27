<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Custom platform-level expense categories (super-admin only).
     *
     * Supplements the built-in MasterExpense::CATEGORIES list so the operator
     * can capture costs that don't fit the default buckets without having to
     * fragment the chart breakdown via free-text. Slugs in `key` are unique
     * across the platform (no clinic scope — master finance is global).
     */
    public function up(): void
    {
        Schema::create('master_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('label', 80);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_expense_categories');
    }
};
