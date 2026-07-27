<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateClinicQuick extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:create-quick 
                            {name : Clinic name}
                            {email : Clinic email}
                            {admin_first_name : Admin first name}
                            {admin_last_name : Admin last name}
                            {admin_email : Admin email}
                            {admin_password : Admin password}
                            {--phone= : Clinic phone}
                            {--address= : Clinic address}
                            {--max-users=50 : Maximum users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quickly create a new clinic with an admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Creating new clinic...");
        $this->newLine();

        DB::beginTransaction();
        try {
            // Create clinic
            $clinic = Clinic::create([
                'name' => $this->argument('name'),
                'email' => $this->argument('email'),
                'phone' => $this->option('phone'),
                'address' => $this->option('address'),
                'max_users' => $this->option('max-users'),
                'is_active' => true,
                'activated_at' => now(),
            ]);

            $this->info("✅ Clinic created:");
            $this->info("   ID: {$clinic->id}");
            $this->info("   Name: {$clinic->name}");
            $this->info("   Email: {$clinic->email}");
            $this->newLine();

            // Create admin user
            $admin = User::create([
                'first_name' => $this->argument('admin_first_name'),
                'last_name' => $this->argument('admin_last_name'),
                'email' => $this->argument('admin_email'),
                'username' => $this->argument('admin_email'),
                'password' => Hash::make($this->argument('admin_password')),
                'role' => 'admin',
                'clinic_id' => $clinic->id,
                'is_active' => true,
                'activated_at' => now(),
                'language' => 'en',
            ]);

            $this->info("✅ Admin user created:");
            $this->info("   ID: {$admin->id}");
            $this->info("   Name: {$admin->first_name} {$admin->last_name}");
            $this->info("   Email: {$admin->email}");
            $this->info("   Role: {$admin->role}");
            $this->info("   Password: {$this->argument('admin_password')}");
            $this->newLine();

            DB::commit();

            $this->info("🎉 Clinic and admin user created successfully!");
            $this->newLine();
            $this->info("📋 Summary:");
            $this->info("   Clinic ID: {$clinic->id}");
            $this->info("   Clinic Name: {$clinic->name}");
            $this->info("   Admin Email: {$admin->email}");
            $this->info("   Admin Password: {$this->argument('admin_password')}");
            $this->newLine();
            $this->info("The admin can now log in with full access to all features.");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error creating clinic: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}

