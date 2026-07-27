<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $countries = [
            ['name' => 'Iraq',                'iso_code' => 'IQ', 'default_language' => 'ar', 'timezone' => 'Asia/Baghdad'],
            ['name' => 'Egypt',               'iso_code' => 'EG', 'default_language' => 'ar', 'timezone' => 'Africa/Cairo'],
            ['name' => 'Jordan',              'iso_code' => 'JO', 'default_language' => 'ar', 'timezone' => 'Asia/Amman'],
            ['name' => 'United Arab Emirates', 'iso_code' => 'AE', 'default_language' => 'ar', 'timezone' => 'Asia/Dubai'],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->updateOrInsert(
                ['iso_code' => $country['iso_code']],
                array_merge($country, [
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Set Iraq as default country for all clinics that don't have a country assigned
        $iraqId = DB::table('countries')->where('iso_code', 'IQ')->value('id');

        if ($iraqId) {
            DB::table('clinics')
                ->whereNull('country_id')
                ->update(['country_id' => $iraqId]);
        }
    }

    public function down(): void
    {
        // Reset clinics that were auto-assigned Iraq
        $iraqId = DB::table('countries')->where('iso_code', 'IQ')->value('id');
        if ($iraqId) {
            DB::table('clinics')
                ->where('country_id', $iraqId)
                ->update(['country_id' => null]);
        }

        // Don't delete the countries — they may have schedules attached
    }
};

