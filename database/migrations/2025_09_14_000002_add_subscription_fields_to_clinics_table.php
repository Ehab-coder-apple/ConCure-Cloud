<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('activation_code')->constrained('subscription_plans')->nullOnDelete();
            $table->enum('billing_cycle', ['monthly','yearly'])->default('monthly')->after('plan_id');
            $table->timestamp('next_billing_at')->nullable()->after('billing_cycle');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id','billing_cycle','next_billing_at']);
        });
    }
};

