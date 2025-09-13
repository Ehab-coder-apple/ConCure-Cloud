<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concure:create-super-admin
                            {--email= : Email address for the super admin}
                            {--password= : Password for the super admin}
                            {--name= : Full name for the super admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user for ConCure SaaS management';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating ConCure Super Admin...');

        // Check if super admin already exists
        if (User::where('role', 'super_admin')->exists()) {
            $this->error('A super admin already exists!');

            if ($this->confirm('Do you want to create another super admin?')) {
                // Continue
            } else {
                return 1;
            }
        }

        // Get user input
        $email = $this->option('email') ?: $this->ask('Email address');
        $password = $this->option('password') ?: $this->secret('Password (min 8 characters)');
        $name = $this->option('name') ?: $this->ask('Full name');

        // Validate input
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'name' => $name,
        ], [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'name' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        // Split name into first and last name
        $nameParts = explode(' ', trim($name), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        // Create username from email
        $username = explode('@', $email)[0];
        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        try {
            // Create super admin user
            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'username' => $username,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
                'activated_at' => now(),
                'clinic_id' => null, // Super admin doesn't belong to any clinic
            ]);

            $this->info('✅ Super admin created successfully!');
            $this->table(['Field', 'Value'], [
                ['Name', $user->full_name],
                ['Email', $user->email],
                ['Username', $user->username],
                ['Role', $user->role],
                ['Status', 'Active'],
            ]);

            $this->info('🌐 You can now access the master dashboard at: /master/login');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create super admin: ' . $e->getMessage());
            return 1;
        }
    }
}
