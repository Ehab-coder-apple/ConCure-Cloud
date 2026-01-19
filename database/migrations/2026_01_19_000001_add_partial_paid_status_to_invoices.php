<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table with the new enum values
        // First, check if we're using SQLite
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Just update existing invoices that have partial payments
            // The status column in SQLite is just TEXT, so we can use any value
            DB::statement("
                UPDATE invoices
                SET status = 'partial_paid'
                WHERE paid_amount > 0
                AND balance > 0
                AND status NOT IN ('cancelled', 'paid')
            ");
        } else {
            // MySQL/PostgreSQL: Modify the enum
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'partial_paid', 'overdue', 'cancelled') DEFAULT 'draft'");

            // Update existing invoices that have partial payments
            DB::statement("
                UPDATE invoices
                SET status = 'partial_paid'
                WHERE paid_amount > 0
                AND balance > 0
                AND status NOT IN ('cancelled', 'paid')
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update partial_paid invoices back to sent
        DB::statement("UPDATE invoices SET status = 'sent' WHERE status = 'partial_paid'");

        // For MySQL/PostgreSQL, remove 'partial_paid' from the enum
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft'");
        }
    }
};

