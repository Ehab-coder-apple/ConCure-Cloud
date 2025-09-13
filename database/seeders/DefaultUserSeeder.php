<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create program owner first (needed for foreign key references)
        $programOwnerId = DB::table('users')->insertGetId([
            'username' => 'program_owner',
            'email' => 'owner@connectpure.com',
            'password' => Hash::make('master123'),
            'first_name' => 'Program',
            'last_name' => 'Owner',
            'phone' => '+1234567890',
            'role' => 'program_owner',
            'is_active' => true,
            'activated_at' => now(),
            'language' => 'en',
            'clinic_id' => null, // Program owner doesn't belong to any clinic
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create demo clinic
        $clinicId = DB::table('clinics')->insertGetId([
            'name' => 'Demo Clinic',
            'email' => 'demo@clinic.com',
            'phone' => '+1234567891',
            'address' => '123 Healthcare Street, Medical City',
            'settings' => json_encode([
                'timezone' => 'UTC',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'working_hours' => ['start' => '08:00', 'end' => '18:00']
            ]),
            'is_active' => true,
            'max_users' => 20,
            'activated_at' => now(),
            'subscription_expires_at' => now()->addYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create demo admin user
        DB::table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@demo.clinic',
            'password' => Hash::make('admin123'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone' => '+1234567892',
            'role' => 'admin',
            'is_active' => true,
            'activated_at' => now(),
            'language' => 'en',
            'clinic_id' => $clinicId,
            'created_by' => $programOwnerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create demo doctor
        DB::table('users')->insert([
            'username' => 'doctor',
            'email' => 'doctor@demo.clinic',
            'password' => Hash::make('doctor123'),
            'first_name' => 'Dr. John',
            'last_name' => 'Smith',
            'phone' => '+1234567893',
            'role' => 'doctor',
            'is_active' => true,
            'activated_at' => now(),
            'language' => 'en',
            'clinic_id' => $clinicId,
            'created_by' => $programOwnerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create activation codes
        DB::table('activation_codes')->insert([
            [
                'code' => 'CLINIC-' . strtoupper(Str::random(8)),
                'type' => 'clinic',
                'clinic_id' => null,
                'role' => null,
                'is_used' => false,
                'used_by' => null,
                'used_at' => null,
                'expires_at' => now()->addMonths(3),
                'created_by' => $programOwnerId,
                'notes' => 'Demo clinic activation code',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'USER-' . strtoupper(Str::random(8)),
                'type' => 'user',
                'clinic_id' => $clinicId,
                'role' => 'doctor',
                'is_used' => false,
                'used_by' => null,
                'used_at' => null,
                'expires_at' => now()->addMonths(1),
                'created_by' => $programOwnerId,
                'notes' => 'Demo doctor activation code',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
