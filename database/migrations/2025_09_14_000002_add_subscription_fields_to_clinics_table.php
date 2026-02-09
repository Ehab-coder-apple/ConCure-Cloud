<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add columns defensively without relying on specific previous columns/order
        if (!Schema::hasColumn('clinics', 'plan_id')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->unsignedBigInteger('plan_id')->nullable();
            });
        }

        // Add FK if table exists (skip if already added)
        try {
            if (Schema::hasTable('subscription_plans')) {
                Schema::table('clinics', function (Blueprint $table) {
                    // Avoid duplicate foreign creation by wrapping in try/catch
                    $table->foreign('plan_id')->references('id')->on('subscription_plans')->nullOnDelete();
                });
            }
        } catch (\Throwable $e) {
            // ignore if already exists
        }

        if (!Schema::hasColumn('clinics', 'billing_cycle')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->enum('billing_cycle', ['monthly','yearly'])->default('monthly');
            });
        }

        if (!Schema::hasColumn('clinics', 'next_billing_at')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->timestamp('next_billing_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Drop FK if present
        try {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropForeign(['plan_id']);
            });
        } catch (\Throwable $e) {
            // ignore
        }

        // Drop columns if they exist
        if (Schema::hasColumn('clinics', 'next_billing_at')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropColumn('next_billing_at');
            });
        }
        if (Schema::hasColumn('clinics', 'billing_cycle')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropColumn('billing_cycle');
            });
        }
        if (Schema::hasColumn('clinics', 'plan_id')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropColumn('plan_id');
            });
        }
    }
};
