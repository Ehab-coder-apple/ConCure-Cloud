<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Console\Command;

class ListClinics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:list {--search=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all clinics with their details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $search = $this->option('search');

        if ($search) {
            $clinics = Clinic::where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->get();
        } else {
            $clinics = Clinic::all();
        }

        if ($clinics->isEmpty()) {
            $this->warn("No clinics found.");
            return 0;
        }

        $this->info("Found {$clinics->count()} clinic(s):");
        $this->newLine();

        $headers = ['ID', 'Name', 'Email', 'Phone', 'Users', 'Max Users', 'Active', 'Created'];
        $rows = [];

        foreach ($clinics as $clinic) {
            $userCount = User::where('clinic_id', $clinic->id)->count();
            $adminCount = User::where('clinic_id', $clinic->id)->where('role', 'admin')->count();
            
            $rows[] = [
                $clinic->id,
                $clinic->name,
                $clinic->email,
                $clinic->phone ?? 'N/A',
                "{$userCount} ({$adminCount} admin)",
                $clinic->max_users ?? 'N/A',
                $clinic->is_active ? 'Yes' : 'No',
                $clinic->created_at->format('Y-m-d'),
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        // Show detailed info for each clinic
        foreach ($clinics as $clinic) {
            $this->info("Clinic ID {$clinic->id}: {$clinic->name}");
            $admins = User::where('clinic_id', $clinic->id)->where('role', 'admin')->get();
            
            if ($admins->isNotEmpty()) {
                $this->info("  Admins:");
                foreach ($admins as $admin) {
                    $this->info("    - {$admin->first_name} {$admin->last_name} ({$admin->email})");
                }
            } else {
                $this->warn("  ⚠️  No admin users found!");
            }
            $this->newLine();
        }

        return 0;
    }
}

