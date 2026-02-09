<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SimpleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the demo clinic ID
        $clinicId = DB::table('clinics')->where('name', 'Demo Clinic')->first()->id;

        $users = [
            [
                'username' => 'program_owner',
                'first_name' => 'Program',
                'last_name' => 'Owner',
                'email' => 'program_owner@concure.com',
                'password' => Hash::make('ConCure2024!'),
                'role' => 'program_owner',
                'is_active' => true,
                'clinic_id' => $clinicId,
                'language' => 'en',
                'activated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'admin',
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'email' => 'admin@concure.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'clinic_id' => $clinicId,
                'language' => 'en',
                'activated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'doctor',
                'first_name' => 'Dr. John',
                'last_name' => 'Smith',
                'email' => 'doctor@concure.com',
                'password' => Hash::make('doctor123'),
                'role' => 'doctor',
                'is_active' => true,
                'clinic_id' => $clinicId,
                'language' => 'en',
                'activated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'assistant',
                'first_name' => 'Medical',
                'last_name' => 'Assistant',
                'email' => 'assistant@concure.com',
                'password' => Hash::make('assistant123'),
                'role' => 'assistant',
                'is_active' => true,
                'clinic_id' => $clinicId,
                'language' => 'en',
                'activated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'nurse',
                'first_name' => 'Head',
                'last_name' => 'Nurse',
                'email' => 'nurse@concure.com',
                'password' => Hash::make('nurse123'),
                'role' => 'nurse',
                'is_active' => true,
                'clinic_id' => $clinicId,
                'language' => 'en',
                'activated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'accountant',
                'first_name' => 'Financial',
                'last_name' => 'Accountant',
                'email' => 'accountant@concure.com',
                'password' => Hash::make('accountant123'),
                'role' => 'accountant',
                'is_active' => true,
                'clinic_id' => $clinicId,
                'language' => 'en',
                'activated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }
    }
}
