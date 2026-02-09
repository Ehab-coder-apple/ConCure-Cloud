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
        // Check if columns exist before trying to drop them
        if (Schema::hasColumn('clinics', 'subscription_status')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropColumn([
                    'subscription_status',
                    'subscription_expires_at',
                    'trial_started_at',
                    'trial_expires_at',
                    'is_trial'
                ]);
            });
        } else {
            echo "Subscription fields do not exist in clinics table - skipping removal.\n";
        }

        // Log the migration
        DB::table('audit_logs')->insert([
            'user_id' => null,
            'clinic_id' => null,
            'action' => 'system_migration',
            'model_type' => 'Clinic',
            'model_id' => null,
            'description' => 'Removed subscription system fields from clinics table',
            'old_values' => null,
            'new_values' => json_encode([
                'migration' => 'remove_subscription_fields',
                'removed_fields' => [
                    'subscription_status',
                    'subscription_expires_at',
                    'trial_started_at',
                    'trial_expires_at',
                    'is_trial'
                ],
                'reason' => 'Subscription system no longer needed'
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Migration',
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "Removed subscription fields from clinics table\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Restore subscription-related fields
            $table->string('subscription_status')->nullable()->after('activated_at');
            $table->date('subscription_expires_at')->nullable()->after('subscription_status');
            $table->timestamp('trial_started_at')->nullable()->after('subscription_expires_at');
            $table->timestamp('trial_expires_at')->nullable()->after('trial_started_at');
            $table->boolean('is_trial')->default(false)->after('trial_expires_at');
        });

        // Log the rollback
        DB::table('audit_logs')->insert([
            'user_id' => null,
            'clinic_id' => null,
            'action' => 'system_migration_rollback',
            'model_type' => 'Clinic',
            'model_id' => null,
            'description' => 'Restored subscription system fields to clinics table',
            'changes' => json_encode([
                'migration' => 'remove_subscription_fields_rollback',
                'restored_fields' => [
                    'subscription_status',
                    'subscription_expires_at',
                    'trial_started_at',
                    'trial_expires_at',
                    'is_trial'
                ]
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Migration Rollback',
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        echo "Restored subscription fields to clinics table\n";
    }
};
