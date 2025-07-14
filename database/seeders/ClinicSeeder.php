<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('clinics')->insert([
            'name' => 'Demo Clinic',
            'address' => '123 Medical Center Drive',
            'phone' => '+1-555-0123',
            'email' => 'info@democlinic.com',
            'is_active' => true,
            'max_users' => 50,
            'activated_at' => now(),
            'subscription_expires_at' => now()->addYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
