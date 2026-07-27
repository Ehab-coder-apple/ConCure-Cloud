<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aesthetic_sessions', function (Blueprint $table) {
            $table->date('next_due_date')->nullable()->after('session_date');
            $table->index(['tenant_id', 'next_due_date'], 'aesthetic_sessions_tenant_next_due_index');
        });
    }

    public function down(): void
    {
        Schema::table('aesthetic_sessions', function (Blueprint $table) {
            $table->dropIndex('aesthetic_sessions_tenant_next_due_index');
            $table->dropColumn('next_due_date');
        });
    }
};