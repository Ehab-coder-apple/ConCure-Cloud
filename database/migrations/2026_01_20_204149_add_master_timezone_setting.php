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
        // Add master_timezone setting for super admin
        DB::table('settings')->insert([
            'clinic_id' => null,
            'key' => 'master_timezone',
            'value' => 'UTC',
            'type' => 'string',
            'description' => 'Master admin timezone',
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
        // Remove master_timezone setting
        DB::table('settings')
            ->whereNull('clinic_id')
            ->where('key', 'master_timezone')
            ->delete();
    }
};
