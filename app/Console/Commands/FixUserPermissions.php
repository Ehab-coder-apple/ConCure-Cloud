<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixUserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:fix-permissions {user_id} {--role=doctor} {--full-access}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix user permissions by granting appropriate access based on role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return 1;
        }

        $this->info("Current User Info:");
        $this->info("ID: {$user->id}");
        $this->info("Name: {$user->first_name} {$user->last_name}");
        $this->info("Email: {$user->email}");
        $this->info("Role: {$user->role}");
        $this->info("Clinic ID: {$user->clinic_id}");
        $this->info("Current Permissions: " . json_encode($user->permissions));
        $this->info("Is Clinic Admin: " . ($user->isClinicAdmin() ? 'Yes' : 'No'));
        $this->newLine();

        if ($this->option('full-access')) {
            // Change role to admin for full access
            if ($this->confirm("Change user role to 'admin' for full clinic access?")) {
                $user->role = 'admin';
                $user->save();
                $this->info("✅ User role changed to 'admin'. They now have full access to all features.");
                return 0;
            }
        }

        // Grant doctor-specific permissions
        $doctorPermissions = [
            'patients_view',
            'patients_create',
            'patients_edit',
            'patients_files',
            'patients_history',
            'appointments_view',
            'appointments_create',
            'appointments_edit',
            'prescriptions_view',
            'prescriptions_create',
            'prescriptions_edit',
            'lab_view',
            'lab_create',
            'radiology_view',
            'radiology_create',
            'nutrition_view',
            'nutrition_create',
            'nutrition_edit',
            'medicines_view',
            'dashboard_view',
        ];

        if ($this->confirm("Grant standard doctor permissions to this user?")) {
            $user->permissions = $doctorPermissions;
            $user->save();
            $this->info("✅ Doctor permissions granted successfully!");
            $this->info("Granted permissions: " . implode(', ', $doctorPermissions));
            return 0;
        }

        $this->warn("No changes made.");
        return 0;
    }
}

