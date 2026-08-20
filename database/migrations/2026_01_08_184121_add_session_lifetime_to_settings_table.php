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
        // Insert session lifetime setting for global configuration
        DB::table('settings')->insert([
            'clinic_id' => null, // Global setting
            'key' => 'session_lifetime',
            'value' => '20', // Secure default: 20 minutes (admin can override in System Settings)
            'type' => 'integer',
            'description' => 'Session lifetime in minutes (how long users can stay logged in without activity)',
            'is_public' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove session lifetime setting
        DB::table('settings')
            ->where('key', 'session_lifetime')
            ->whereNull('clinic_id')
            ->delete();
    }
};
