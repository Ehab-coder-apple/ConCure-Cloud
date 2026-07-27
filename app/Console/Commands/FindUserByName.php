<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Console\Command;

class FindUserByName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:find {search?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find users by name, email, or list all users with limited access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $search = $this->argument('search');

        if ($search) {
            // Search for specific user
            $users = User::where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->get();

            if ($users->isEmpty()) {
                $this->error("No users found matching '{$search}'");
                return 1;
            }
        } else {
            // Find all users with no permissions or limited access
            $this->info("Finding users with no permissions or limited access...");
            $users = User::whereNotIn('role', ['super_admin', 'admin'])->get();

            // Filter users with no permissions (SQLite compatible)
            $users = $users->filter(function($user) {
                return empty($user->permissions) || count($user->permissions) == 0;
            });

            if ($users->isEmpty()) {
                $this->info("No users found with limited access. Showing all non-admin users:");
                $users = User::whereNotIn('role', ['super_admin', 'admin'])->take(20)->get();
            }
        }

        $this->info("Found " . $users->count() . " user(s):");
        $this->newLine();

        $headers = ['ID', 'Name', 'Email', 'Role', 'Clinic ID', 'Clinic Name', 'Permissions', 'Is Admin'];
        $rows = [];

        foreach ($users as $user) {
            $clinic = $user->clinic_id ? Clinic::find($user->clinic_id) : null;
            $permCount = is_array($user->permissions) ? count($user->permissions) : 0;
            
            $rows[] = [
                $user->id,
                $user->first_name . ' ' . $user->last_name,
                $user->email,
                $user->role,
                $user->clinic_id ?? 'N/A',
                $clinic ? $clinic->name : 'N/A',
                $permCount . ' perms',
                $user->isClinicAdmin() ? 'Yes' : 'No',
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();
        $this->info("To fix a user's permissions, run:");
        $this->info("  php artisan user:fix-permissions {user_id} --full-access");
        $this->info("  OR");
        $this->info("  php artisan user:fix-permissions {user_id}");

        return 0;
    }
}

