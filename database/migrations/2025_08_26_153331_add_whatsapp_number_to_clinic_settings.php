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
        // Add WhatsApp number setting for existing clinics
        // This migration ensures all clinics have the whatsapp_number setting available

        // Get all clinic IDs
        $clinicIds = DB::table('clinics')->pluck('id');

        foreach ($clinicIds as $clinicId) {
            // Check if whatsapp_number setting already exists
            $exists = DB::table('settings')
                ->where('clinic_id', $clinicId)
                ->where('key', 'whatsapp_number')
                ->exists();

            // If it doesn't exist, create it with null value
            if (!$exists) {
                DB::table('settings')->insert([
                    'clinic_id' => $clinicId,
                    'key' => 'whatsapp_number',
                    'value' => null,
                    'type' => 'string',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove WhatsApp number settings
        DB::table('settings')
            ->where('key', 'whatsapp_number')
            ->delete();
    }
};
