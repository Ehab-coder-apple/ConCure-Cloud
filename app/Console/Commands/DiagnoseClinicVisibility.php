<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Models\User;

class DiagnoseClinicVisibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:diagnose {user_email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose why a clinic might not appear in Master clinic list';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('user_email');

        if ($email) {
            // Diagnose specific user's clinic
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("User with email '{$email}' not found.");
                return 1;
            }

            $this->info("User: {$user->first_name} {$user->last_name} ({$user->email})");
            $this->info("Clinic ID: {$user->clinic_id}");
            $this->info("Role: {$user->role}");
            $this->newLine();

            if (!$user->clinic_id) {
                $this->error("This user has no clinic_id assigned!");
                return 1;
            }

            $clinic = Clinic::find($user->clinic_id);
            
            if (!$clinic) {
                $this->error("Clinic ID {$user->clinic_id} does not exist in clinics table!");
                return 1;
            }

            $this->displayClinicInfo($clinic);
        } else {
            // Show all clinics summary
            $this->info("=== All Clinics Summary ===");
            $this->newLine();
            
            $allClinics = Clinic::all();
            $this->info("Total clinics in database: {$allClinics->count()}");
            $this->newLine();

            foreach ($allClinics as $clinic) {
                $this->displayClinicInfo($clinic);
                $this->newLine();
            }
        }

        return 0;
    }

    private function displayClinicInfo($clinic)
    {
        $this->info("Clinic: {$clinic->name} (ID: {$clinic->id})");
        $this->info("Email: " . ($clinic->email ?? 'N/A'));
        $this->info("Phone: " . ($clinic->phone ?? 'N/A'));
        $this->info("Status: " . ($clinic->is_active ? '✅ ACTIVE' : '❌ INACTIVE'));
        $this->info("Type: " . ($clinic->is_demo ? '🎭 DEMO' : '🏢 TENANT'));
        $this->info("Activated At: " . ($clinic->activated_at ? $clinic->activated_at->format('Y-m-d H:i:s') : 'NOT ACTIVATED'));
        $this->info("Users Count: " . $clinic->users()->count());
        
        $admins = $clinic->users()->where('role', 'admin')->get();
        if ($admins->count() > 0) {
            $this->info("Admin(s):");
            foreach ($admins as $admin) {
                $this->info("  - {$admin->first_name} {$admin->last_name} ({$admin->email})");
            }
        } else {
            $this->warn("  ⚠️  No admin user found for this clinic!");
        }
    }
}
