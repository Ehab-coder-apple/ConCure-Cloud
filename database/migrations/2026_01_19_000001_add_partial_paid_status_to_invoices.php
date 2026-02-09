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
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: We need to recreate the table because we can't modify CHECK constraints
            // Step 1: Drop existing indexes
            DB::statement('DROP INDEX IF EXISTS invoices_clinic_id_invoice_date_index');
            DB::statement('DROP INDEX IF EXISTS invoices_patient_id_status_index');
            DB::statement('DROP INDEX IF EXISTS invoices_invoice_number_index');
            DB::statement('DROP INDEX IF EXISTS invoices_invoice_number_unique');

            // Step 2: Rename the old table
            DB::statement('ALTER TABLE invoices RENAME TO invoices_old');

            // Step 3: Create the new table with updated CHECK constraint
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->unsignedBigInteger('patient_id');
                $table->unsignedBigInteger('clinic_id');
                $table->date('invoice_date');
                $table->date('due_date')->nullable();
                $table->decimal('subtotal', 10, 2);
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->decimal('discount_rate', 5, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('total_amount', 10, 2);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('balance', 10, 2);
                $table->enum('status', ['draft', 'sent', 'paid', 'partial_paid', 'overdue', 'cancelled'])->default('draft');
                $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'insurance', 'other'])->nullable();
                $table->text('notes')->nullable();
                $table->text('terms')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index(['clinic_id', 'invoice_date']);
                $table->index(['patient_id', 'status']);
                $table->index('invoice_number');
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
                $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });

            // Step 4: Copy data from old table to new table
            DB::statement('INSERT INTO invoices SELECT * FROM invoices_old');

            // Step 5: Drop the old table
            DB::statement('DROP TABLE invoices_old');

            // Step 6: Update existing invoices that have partial payments
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

