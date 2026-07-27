<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteClinic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:delete {clinic_id} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a clinic and all its associated data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clinicId = $this->argument('clinic_id');
        $clinic = Clinic::find($clinicId);

        if (!$clinic) {
            $this->error("Clinic with ID {$clinicId} not found!");
            return 1;
        }

        $this->warn("⚠️  WARNING: This will delete the following clinic and ALL associated data:");
        $this->info("Clinic ID: {$clinic->id}");
        $this->info("Clinic Name: {$clinic->name}");
        $this->info("Clinic Email: {$clinic->email}");
        $this->newLine();

        // Count associated data
        $userCount = User::where('clinic_id', $clinicId)->count();
        $this->warn("This will delete:");
        $this->warn("  - {$userCount} user(s)");
        $this->warn("  - All patients");
        $this->warn("  - All appointments");
        $this->warn("  - All prescriptions");
        $this->warn("  - All financial records");
        $this->warn("  - All settings");
        $this->warn("  - All other clinic data");
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you absolutely sure you want to delete this clinic?')) {
                $this->info('Deletion cancelled.');
                return 0;
            }

            if (!$this->confirm('This action CANNOT be undone. Continue?')) {
                $this->info('Deletion cancelled.');
                return 0;
            }
        }

        DB::beginTransaction();
        try {
            // Delete users (cascade will handle most related data)
            $deletedUsers = User::where('clinic_id', $clinicId)->delete();
            $this->info("✅ Deleted {$deletedUsers} user(s)");

            // Delete clinic (cascade should handle remaining data)
            $clinic->delete();
            $this->info("✅ Deleted clinic: {$clinic->name}");

            DB::commit();
            $this->info("🎉 Clinic deleted successfully!");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error deleting clinic: " . $e->getMessage());
            return 1;
        }
    }
}

