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
        // Find users without clinic_id
        $usersWithoutClinic = DB::table('users')->whereNull('clinic_id')->get();
        
        if ($usersWithoutClinic->count() > 0) {
            // Get or create a default clinic
            $defaultClinic = DB::table('clinics')->first();
            
            if (!$defaultClinic) {
                // Create default clinic
                $clinicId = DB::table('clinics')->insertGetId([
                    'name' => 'Default Clinic',
                    'email' => 'admin@defaultclinic.com',
                    'phone' => '123456789',
                    'address' => 'Default Address',
                    'is_active' => true,
                    'activated_at' => now(),
                    'subscription_expires_at' => now()->addYear(),
                    'max_users' => 50,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                echo "Created default clinic with ID: {$clinicId}\n";
            } else {
                $clinicId = $defaultClinic->id;
                echo "Using existing clinic with ID: {$clinicId}\n";
            }
            
            // Update users without clinic_id
            $updatedCount = DB::table('users')
                ->whereNull('clinic_id')
                ->update([
                    'clinic_id' => $clinicId,
                    'updated_at' => now()
                ]);
            
            echo "Updated {$updatedCount} users with clinic_id: {$clinicId}\n";
            
            // Log the migration
            DB::table('audit_logs')->insert([
                'user_id' => null,
                'clinic_id' => $clinicId,
                'action' => 'system_migration',
                'model_type' => 'User',
                'model_id' => null,
                'description' => 'Fixed users without clinic_id assignment',
                'old_values' => null,
                'new_values' => json_encode([
                    'migration' => 'fix_users_without_clinic_id',
                    'affected_users' => $updatedCount,
                    'assigned_clinic_id' => $clinicId
                ]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'System Migration',
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            echo "No users found without clinic_id\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed as we don't know
        // which users originally had null clinic_id
        echo "Cannot reverse this migration automatically\n";
    }
};
