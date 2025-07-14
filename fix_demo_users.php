<?php
/**
 * Fix Demo Users - Create admin and doctor users if they don't exist
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== ConCure Demo Users Fix ===" . PHP_EOL;

try {
    // Check if users exist
    $adminUser = \App\Models\User::where('username', 'admin')->first();
    $doctorUser = \App\Models\User::where('username', 'doctor')->first();
    
    echo "Checking existing users..." . PHP_EOL;
    echo "Admin user exists: " . ($adminUser ? "✅ Yes" : "❌ No") . PHP_EOL;
    echo "Doctor user exists: " . ($doctorUser ? "✅ Yes" : "❌ No") . PHP_EOL;
    
    // Create or get default clinic
    $clinic = \App\Models\Clinic::first();
    if (!$clinic) {
        echo "Creating default clinic..." . PHP_EOL;
        $clinic = \App\Models\Clinic::create([
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
    
    // Create admin user if doesn't exist
    if (!$adminUser) {
        echo "Creating admin user..." . PHP_EOL;
        $adminUser = \App\Models\User::create([
            'username' => 'admin',
            'email' => 'admin@demo.clinic',
            'password' => bcrypt('admin123'),
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
    } else {
        echo "✅ Admin user already exists, updating..." . PHP_EOL;
        $adminUser->update([
            'password' => bcrypt('admin123'),
            'is_active' => true,
            'activated_at' => now(),
            'clinic_id' => $clinic->id,
        ]);
    }
    
    // Create doctor user if doesn't exist
    if (!$doctorUser) {
        echo "Creating doctor user..." . PHP_EOL;
        $doctorUser = \App\Models\User::create([
            'username' => 'doctor',
            'email' => 'doctor@demo.clinic',
            'password' => bcrypt('doctor123'),
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
    } else {
        echo "✅ Doctor user already exists, updating..." . PHP_EOL;
        $doctorUser->update([
            'password' => bcrypt('doctor123'),
            'is_active' => true,
            'activated_at' => now(),
            'clinic_id' => $clinic->id,
        ]);
    }
    
    echo PHP_EOL . "=== SUCCESS! Demo users are ready ===" . PHP_EOL;
    echo "You can now log in with:" . PHP_EOL;
    echo "👤 Admin - Username: admin, Password: admin123" . PHP_EOL;
    echo "👨‍⚕️ Doctor - Username: doctor, Password: doctor123" . PHP_EOL;
    echo PHP_EOL;
    echo "Or use the demo login URLs:" . PHP_EOL;
    echo "🔗 Admin: http://127.0.0.1:8001/dev/login-admin" . PHP_EOL;
    echo "🔗 Doctor: http://127.0.0.1:8001/dev/login-doctor" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
