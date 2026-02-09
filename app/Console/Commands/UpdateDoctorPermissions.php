<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateDoctorPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:grant-lab-requests {user_id? : User ID to grant permissions to} {--all-doctors : Grant to all doctors} {--all-admins : Grant to all admins}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant lab request creation permissions to users (admin-delegated permissions)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $allDoctors = $this->option('all-doctors');
        $allAdmins = $this->option('all-admins');

        $requiredPermissions = [
            'prescriptions_view',
            'prescriptions_create',
            'prescriptions_edit',
            'prescriptions_print',
        ];

        $users = collect();

        if ($userId) {
            // Grant to specific user
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
            $users->push($user);
            $this->info("Granting lab request permissions to: {$user->full_name} ({$user->role})");

        } elseif ($allDoctors) {
            // Grant to all doctors
            $users = User::where('role', 'doctor')->get();
            $this->info("Granting lab request permissions to all doctors...");

        } elseif ($allAdmins) {
            // Grant to all admins
            $users = User::where('role', 'admin')->get();
            $this->info("Granting lab request permissions to all admins...");

        } else {
            // Interactive mode - show available users
            $this->info('Available users:');
            $allUsers = User::select('id', 'first_name', 'last_name', 'role')->get();

            foreach ($allUsers as $user) {
                $hasPermission = $user->hasPermission('prescriptions_create') ? '✅' : '❌';
                $this->line("{$user->id}: {$user->full_name} ({$user->role}) {$hasPermission}");
            }

            $selectedId = $this->ask('Enter user ID to grant lab request permissions (or press Enter to cancel)');

            if (!$selectedId) {
                $this->info('Operation cancelled.');
                return 0;
            }

            $user = User::find($selectedId);
            if (!$user) {
                $this->error("User with ID {$selectedId} not found.");
                return 1;
            }

            $users->push($user);
        }

        if ($users->isEmpty()) {
            $this->warn('No users found to update.');
            return 0;
        }

        $updated = 0;

        foreach ($users as $user) {
            $currentPermissions = $user->permissions ?? [];
            $needsUpdate = false;

            foreach ($requiredPermissions as $permission) {
                if (!in_array($permission, $currentPermissions)) {
                    $currentPermissions[] = $permission;
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                $user->permissions = array_unique($currentPermissions);
                $user->save();
                $updated++;
                $this->line("✅ Updated permissions for: {$user->full_name} ({$user->role})");
            } else {
                $this->line("⏭️  {$user->full_name} already has lab request permissions");
            }
        }

        $this->info("Updated {$updated} user(s) with lab request permissions.");
        $this->info('These users can now create lab requests and prescriptions.');

        return 0;
    }
}
