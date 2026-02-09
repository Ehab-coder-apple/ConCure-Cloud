<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Examples:
     *  php artisan concure:reset-super-admin --email="admin@example.com" --password="NewStrongPass123!"
     *  php artisan concure:reset-super-admin --email="admin@example.com" --create-if-missing --name="Jane Doe"
     */
    protected $signature = 'concure:reset-super-admin
                            {--email= : Email of the super admin to reset or create}
                            {--password= : New password to set}
                            {--name= : Full name when creating a new super admin}
                            {--username= : Username when creating a new super admin}
                            {--create-if-missing : Create the user if not found}';

    /**
     * The console command description.
     */
    protected $description = 'Reset password and ensure role/activation for the Super Admin. Optionally create if missing.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('ConCure • Super Admin reset');

        // Gather inputs (interactive fallback)
        $email = $this->option('email') ?: $this->ask('Email address');
        $password = $this->option('password') ?: $this->secret('New password (min 8 characters)');
        $createIfMissing = (bool)$this->option('create-if-missing');

        // Validate inputs
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            if (!$createIfMissing && !$this->confirm("No user found for {$email}. Create a new Super Admin?")) {
                $this->warn('Aborted.');
                return 1;
            }

            $name = $this->option('name') ?: $this->ask('Full name');
            $username = $this->option('username') ?: explode('@', $email)[0];

            // Split name
            $parts = preg_split('/\s+/', trim((string)$name), 2);
            $firstName = $parts[0] ?? 'Super';
            $lastName = $parts[1] ?? 'Admin';

            // Ensure unique username
            $base = $username;
            $i = 1;
            while (User::where('username', $username)->exists()) {
                $username = $base . $i;
                $i++;
            }

            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'username' => $username,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
                'activated_at' => now(),
                'clinic_id' => null,
            ]);

            $this->info('✅ Created new Super Admin user.');
        } else {
            $this->info("Found existing user #{$user->id} ({$user->email}). Updating...");

            // Update existing user
            $user->password = Hash::make($password);
            $user->role = 'super_admin';
            $user->is_active = true;
            $user->activated_at = now();
            $user->clinic_id = null; // Super admin not tied to a clinic
            $user->save();

            $this->info('✅ Password reset and role/activation enforced.');
        }

        // Show summary
        $this->table(['Field', 'Value'], [
            ['Name', $user->full_name],
            ['Email', $user->email],
            ['Username', $user->username],
            ['Role', $user->role],
            ['Active', $user->is_active ? 'Yes' : 'No'],
            ['Activated At', optional($user->activated_at)->toDateTimeString()],
        ]);

        $this->info('You can now log in at /master/login');
        return 0;
    }
}

