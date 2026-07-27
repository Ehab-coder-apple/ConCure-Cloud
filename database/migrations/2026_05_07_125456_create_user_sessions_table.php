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
        if (!Schema::hasTable('user_sessions')) {
            Schema::create('user_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('credential_used'); // username or email used for login
                $table->string('session_id'); // Laravel session ID
                $table->string('ip_address')->nullable();
                $table->string('device_fingerprint')->nullable(); // hash of browser + OS + IP
                $table->string('user_agent')->nullable(); // full user agent string for reference
                $table->string('browser')->nullable(); // parsed browser name
                $table->string('os')->nullable(); // parsed OS name
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('terminated_at')->nullable();
                $table->string('termination_reason')->nullable(); // 'new_login_elsewhere', 'manual_logout', etc.
                $table->string('terminated_by_session_id')->nullable(); // session_id of the login that caused termination

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'credential_used']);
                $table->index(['session_id']);
                $table->index('terminated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
