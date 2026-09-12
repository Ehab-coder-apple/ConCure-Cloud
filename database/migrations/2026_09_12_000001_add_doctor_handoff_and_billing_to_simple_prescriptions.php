<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Additive, nullable columns supporting the Quick Visit "Send to
     * Doctor" hand-off workflow and a direct consultation cost that gets
     * billed via the generic Invoice module. All nullable/defaulted so the
     * existing (non-quick-visit) prescription flow is unaffected.
     */
    public function up(): void
    {
        Schema::table('simple_prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('simple_prescriptions', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('doctor_id');
            }
            if (!Schema::hasColumn('simple_prescriptions', 'sent_to_doctor')) {
                $table->boolean('sent_to_doctor')->default(false)->after('status');
            }
            if (!Schema::hasColumn('simple_prescriptions', 'sent_to_doctor_at')) {
                $table->timestamp('sent_to_doctor_at')->nullable()->after('sent_to_doctor');
            }
            if (!Schema::hasColumn('simple_prescriptions', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('sent_to_doctor_at');
            }
            if (!Schema::hasColumn('simple_prescriptions', 'invoice_id')) {
                $table->unsignedBigInteger('invoice_id')->nullable()->after('reviewed_at');
            }
        });

        // Foreign keys added separately so a missing/renamed users or
        // invoices table in older installs doesn't break the additive
        // column creation above.
        Schema::table('simple_prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('simple_prescriptions', 'created_by') && Schema::hasTable('users')) {
                try {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                } catch (\Throwable $e) {
                    // Constraint may already exist or DB may not support it (e.g. sqlite re-run); ignore.
                }
            }
            if (Schema::hasColumn('simple_prescriptions', 'invoice_id') && Schema::hasTable('invoices')) {
                try {
                    $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
                } catch (\Throwable $e) {
                    // Ignore if already present.
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simple_prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('simple_prescriptions', 'invoice_id')) {
                try { $table->dropForeign(['invoice_id']); } catch (\Throwable $e) {}
                $table->dropColumn('invoice_id');
            }
            if (Schema::hasColumn('simple_prescriptions', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('simple_prescriptions', 'sent_to_doctor_at')) {
                $table->dropColumn('sent_to_doctor_at');
            }
            if (Schema::hasColumn('simple_prescriptions', 'sent_to_doctor')) {
                $table->dropColumn('sent_to_doctor');
            }
            if (Schema::hasColumn('simple_prescriptions', 'created_by')) {
                try { $table->dropForeign(['created_by']); } catch (\Throwable $e) {}
                $table->dropColumn('created_by');
            }
        });
    }
};
