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
        // First, update any existing program_owner users to admin role
        DB::table('users')
            ->where('role', 'program_owner')
            ->update([
                'role' => 'admin',
                'updated_at' => now()
            ]);

        // For SQLite, we need to recreate the table to change the enum
        // This is a simplified approach that works for SQLite
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN, so we'll just update the data
            // The enum constraint will be enforced by the application
            echo "Updated program_owner users to admin role. Enum constraint will be enforced by application.\n";
        } else {
            // For other databases, update the enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'assistant', 'nurse', 'accountant', 'patient') NOT NULL");
        }
        
        // Clean up any master control related session data
        // This will be handled automatically as sessions expire
        
        // Log the migration
        DB::table('audit_logs')->insert([
            'user_id' => null,
            'clinic_id' => null,
            'action' => 'system_migration',
            'model_type' => 'User',
            'model_id' => null,
            'description' => 'Removed program_owner role and converted existing program_owner users to admin role',
            'old_values' => null,
            'new_values' => json_encode([
                'migration' => 'remove_program_owner_role',
                'affected_users' => DB::table('users')->where('role', 'admin')->count()
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Migration',
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For SQLite, we can't easily modify the enum, so we'll just log the rollback
        if (DB::getDriverName() === 'sqlite') {
            echo "Rollback noted. Manual intervention required to restore program_owner role.\n";
        } else {
            // Add program_owner back to the enum for other databases
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('program_owner', 'admin', 'doctor', 'assistant', 'nurse', 'accountant', 'patient') NOT NULL");
        }
        
        // Note: We don't automatically convert admin users back to program_owner
        // as this could cause data integrity issues. Manual intervention would be required.
        
        // Log the rollback
        DB::table('audit_logs')->insert([
            'user_id' => null,
            'clinic_id' => null,
            'action' => 'system_migration_rollback',
            'model_type' => 'User',
            'model_id' => null,
            'description' => 'Restored program_owner role to user enum (manual user role updates required)',
            'changes' => json_encode([
                'migration' => 'remove_program_owner_role_rollback',
                'note' => 'Admin users were not automatically converted back to program_owner'
            ]),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System Migration Rollback',
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
};
