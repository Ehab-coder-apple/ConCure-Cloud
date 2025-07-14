<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->boolean('is_trial')->default(false)->after('subscription_expires_at');
            $table->timestamp('trial_started_at')->nullable()->after('is_trial');
            $table->timestamp('trial_expires_at')->nullable()->after('trial_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn(['is_trial', 'trial_started_at', 'trial_expires_at']);
        });
    }
};
