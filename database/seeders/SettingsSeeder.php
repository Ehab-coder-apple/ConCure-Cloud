<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Global settings (clinic_id = null)
            [
                'clinic_id' => null,
                'key' => 'app_name',
                'value' => 'ConCure',
                'type' => 'string',
                'description' => 'Application name',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'company_name',
                'value' => 'Connect Pure',
                'type' => 'string',
                'description' => 'Company name',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'primary_color',
                'value' => '#008080',
                'type' => 'string',
                'description' => 'Primary theme color',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'supported_languages',
                'value' => '["en", "ar", "ku"]',
                'type' => 'json',
                'description' => 'Supported languages',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'default_language',
                'value' => 'en',
                'type' => 'string',
                'description' => 'Default language',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'max_file_size',
                'value' => '10240',
                'type' => 'integer',
                'description' => 'Maximum file upload size in KB',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'allowed_file_types',
                'value' => '["pdf", "jpg", "jpeg", "png", "doc", "docx"]',
                'type' => 'json',
                'description' => 'Allowed file types for upload',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'appointment_duration',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Default appointment duration in minutes',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'working_hours_start',
                'value' => '08:00',
                'type' => 'string',
                'description' => 'Default working hours start time',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'working_hours_end',
                'value' => '18:00',
                'type' => 'string',
                'description' => 'Default working hours end time',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'enable_sms',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable SMS functionality',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'enable_whatsapp',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable WhatsApp functionality',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Default currency',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => null,
                'key' => 'tax_rate',
                'value' => '0',
                'type' => 'decimal',
                'description' => 'Default tax rate percentage',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('settings')->insert($settings);
    }
}
