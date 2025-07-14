<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Support\Facades\Hash;

echo "=== Creating Demo Users for Login ===" . PHP_EOL;

try {
    // Create or get default clinic
    $clinic = Clinic::first();
    if (!$clinic) {
        echo "Creating default clinic..." . PHP_EOL;
        $clinic = Clinic::create([
            'name' => 'Demo Clinic',
            'email' => 'demo@clinic.com',
            'phone' => '123456789',
            'address' => 'Demo Address',
            'is_active' => true,
            'activated_at' => now(),
            'subscription_expires_at' => now()->addYear(),
            'max_users' => 50,
        ]);
        echo "✅ Created clinic: {$clinic->name}" . PHP_EOL;
    } else {
        echo "✅ Using existing clinic: {$clinic->name}" . PHP_EOL;
        
        // Make sure clinic is active
        $clinic->update([
            'is_active' => true,
            'activated_at' => now(),
            'subscription_expires_at' => now()->addYear(),
        ]);
    }
    
    // Delete existing admin user if exists
    User::where('username', 'admin')->delete();
    User::where('email', 'admin@demo.clinic')->delete();
    
    // Create admin user
    echo "Creating admin user..." . PHP_EOL;
    $adminUser = User::create([
        'username' => 'admin',
        'email' => 'admin@demo.clinic',
        'password' => Hash::make('admin123'),
        'first_name' => 'Admin',
        'last_name' => 'User',
        'role' => 'admin',
        'is_active' => true,
        'activated_at' => now(),
        'clinic_id' => $clinic->id,
        'permissions' => [
            'dashboard_view', 'dashboard_stats',
            'patients_view', 'patients_create', 'patients_edit', 'patients_delete',
            'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_delete',
            'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete',
            'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete',
            'users_view', 'users_create', 'users_edit', 'users_delete',
            'settings_view', 'settings_edit',
            'finance_view', 'finance_create', 'finance_edit', 'finance_delete',
            'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete',
            'lab_requests_view', 'lab_requests_create', 'lab_requests_edit', 'lab_requests_delete',
            'ai_advisory_view', 'ai_advisory_use'
        ]
    ]);
    echo "✅ Created admin user: {$adminUser->username}" . PHP_EOL;
    
    // Delete existing doctor user if exists
    User::where('username', 'doctor')->delete();
    User::where('email', 'doctor@demo.clinic')->delete();
    
    // Create doctor user
    echo "Creating doctor user..." . PHP_EOL;
    $doctorUser = User::create([
        'username' => 'doctor',
        'email' => 'doctor@demo.clinic',
        'password' => Hash::make('doctor123'),
        'first_name' => 'Dr. John',
        'last_name' => 'Smith',
        'role' => 'doctor',
        'is_active' => true,
        'activated_at' => now(),
        'clinic_id' => $clinic->id,
        'permissions' => [
            'dashboard_view', 'dashboard_stats',
            'patients_view', 'patients_create', 'patients_edit',
            'prescriptions_view', 'prescriptions_create', 'prescriptions_edit',
            'appointments_view', 'appointments_create', 'appointments_edit',
            'medicines_view',
            'nutrition_view', 'nutrition_create', 'nutrition_edit',
            'lab_requests_view', 'lab_requests_create', 'lab_requests_edit',
            'ai_advisory_view', 'ai_advisory_use'
        ]
    ]);
    echo "✅ Created doctor user: {$doctorUser->username}" . PHP_EOL;
    
    echo PHP_EOL . "=== SUCCESS! Demo users are ready ===" . PHP_EOL;
    echo "You can now log in with:" . PHP_EOL;
    echo "👤 Admin - Username: admin, Password: admin123" . PHP_EOL;
    echo "👨‍⚕️ Doctor - Username: doctor, Password: doctor123" . PHP_EOL;
    echo PHP_EOL;
    echo "OR use email:" . PHP_EOL;
    echo "👤 Admin - Email: admin@demo.clinic, Password: admin123" . PHP_EOL;
    echo "👨‍⚕️ Doctor - Email: doctor@demo.clinic, Password: doctor123" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
