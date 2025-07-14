<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ConCureSetup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'concure:setup {--fresh : Fresh installation (drops all tables)}';

    /**
     * The console command description.
     */
    protected $description = 'Set up ConCure Clinic Management System';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏥 Setting up ConCure Clinic Management System...');
        $this->newLine();

        // Check if fresh installation is requested
        $fresh = $this->option('fresh');

        if ($fresh) {
            $this->warn('⚠️  Fresh installation requested - This will drop all existing data!');
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->error('Installation cancelled.');
                return 1;
            }
        }

        // Step 1: Check environment
        $this->info('📋 Step 1: Checking environment...');
        $this->checkEnvironment();

        // Step 2: Create database file if it doesn't exist
        $this->info('🗄️  Step 2: Setting up database...');
        $this->setupDatabase();

        // Step 3: Run migrations
        $this->info('🔧 Step 3: Running database migrations...');
        if ($fresh) {
            Artisan::call('migrate:fresh', ['--force' => true]);
        } else {
            Artisan::call('migrate', ['--force' => true]);
        }
        $this->line(Artisan::output());

        // Step 4: Seed database
        $this->info('🌱 Step 4: Seeding database with initial data...');
        Artisan::call('db:seed', ['--force' => true]);
        $this->line(Artisan::output());

        // Step 5: Create storage link
        $this->info('🔗 Step 5: Creating storage link...');
        if (File::exists(public_path('storage'))) {
            File::delete(public_path('storage'));
        }
        Artisan::call('storage:link');
        $this->line(Artisan::output());

        // Step 6: Set permissions
        $this->info('🔐 Step 6: Setting file permissions...');
        $this->setPermissions();

        // Step 7: Clear caches
        $this->info('🧹 Step 7: Clearing caches...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        // Step 8: Display success message
        $this->displaySuccessMessage();

        return 0;
    }

    /**
     * Check environment requirements.
     */
    private function checkEnvironment()
    {
        $requirements = [
            'PHP Version' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'SQLite Extension' => extension_loaded('sqlite3'),
            'GD Extension' => extension_loaded('gd'),
            'Zip Extension' => extension_loaded('zip'),
            'XML Extension' => extension_loaded('xml'),
        ];

        foreach ($requirements as $requirement => $met) {
            if ($met) {
                $this->line("  ✅ {$requirement}");
            } else {
                $this->error("  ❌ {$requirement} - REQUIRED");
            }
        }

        $failed = array_filter($requirements, fn($met) => !$met);
        if (!empty($failed)) {
            $this->error('Please install missing requirements before continuing.');
            exit(1);
        }

        $this->info('  All requirements met!');
        $this->newLine();
    }

    /**
     * Setup database file.
     */
    private function setupDatabase()
    {
        $dbPath = database_path('database.sqlite');
        
        if (!File::exists($dbPath)) {
            $this->line('  Creating SQLite database file...');
            File::put($dbPath, '');
            chmod($dbPath, 0664);
            $this->info('  ✅ Database file created');
        } else {
            $this->info('  ✅ Database file already exists');
        }

        // Test database connection
        try {
            DB::connection()->getPdo();
            $this->info('  ✅ Database connection successful');
        } catch (\Exception $e) {
            $this->error('  ❌ Database connection failed: ' . $e->getMessage());
            exit(1);
        }

        $this->newLine();
    }

    /**
     * Set file permissions.
     */
    private function setPermissions()
    {
        $paths = [
            storage_path(),
            bootstrap_path('cache'),
            database_path('database.sqlite'),
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                if (is_dir($path)) {
                    chmod($path, 0775);
                    $this->line("  ✅ Set permissions for directory: {$path}");
                } else {
                    chmod($path, 0664);
                    $this->line("  ✅ Set permissions for file: {$path}");
                }
            }
        }

        $this->newLine();
    }

    /**
     * Display success message with login credentials.
     */
    private function displaySuccessMessage()
    {
        $this->newLine();
        $this->info('🎉 ConCure Clinic Management System setup completed successfully!');
        $this->newLine();

        // Display login credentials
        $this->line('📋 <fg=yellow>Default Login Credentials:</>');
        $this->newLine();

        $credentials = [
            ['Role', 'Username', 'Password', 'Access Level'],
            ['Program Owner', 'program_owner', 'ConCure2024!', 'Full System Access'],
            ['Admin', 'admin', 'admin123', 'Clinic Administration'],
            ['Doctor', 'doctor', 'doctor123', 'Patient Management & Prescriptions'],
            ['Assistant', 'assistant', 'assistant123', 'Patient Support'],
            ['Nurse', 'nurse', 'nurse123', 'Patient Care'],
            ['Accountant', 'accountant', 'accountant123', 'Financial Management'],
        ];

        $this->table($credentials[0], array_slice($credentials, 1));

        $this->newLine();
        $this->line('🚀 <fg=green>Next Steps:</>');
        $this->line('  1. Run: <fg=cyan>php artisan serve</fg=cyan>');
        $this->line('  2. Open: <fg=cyan>http://localhost:8000</fg=cyan>');
        $this->line('  3. Login with any of the credentials above');
        $this->line('  4. Change default passwords in Settings');
        $this->newLine();

        $this->line('📚 <fg=blue>Features Available:</>');
        $this->line('  • Patient Management with Medical Records');
        $this->line('  • Prescription & Lab Request System');
        $this->line('  • Diet Planning with Nutrition Database');
        $this->line('  • Financial Management (Invoices & Expenses)');
        $this->line('  • Advertisement Management');
        $this->line('  • Multi-language Support (English, Arabic, Kurdish)');
        $this->line('  • Role-based Access Control');
        $this->line('  • Audit Logging & Activity Monitoring');
        $this->newLine();

        $this->line('💡 <fg=magenta>Tips:</>');
        $this->line('  • Use the language switcher in the top-right corner');
        $this->line('  • Check Settings for system configuration');
        $this->line('  • View audit logs for system activity');
        $this->line('  • Upload patient files and medical documents');
        $this->newLine();

        $this->info('Happy managing your clinic! 🏥✨');
    }
}
