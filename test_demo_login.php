<?php
/**
 * Test Demo Login Functionality
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== ConCure Demo Login Test ===" . PHP_EOL;

try {
    // Check if users exist
    $adminUser = \App\Models\User::where('role', 'admin')->where('is_active', true)->first();
    $doctorUser = \App\Models\User::where('role', 'doctor')->where('is_active', true)->first();
    
    echo "Admin User: " . ($adminUser ? "✅ Found (ID: {$adminUser->id}, Username: {$adminUser->username})" : "❌ Not found") . PHP_EOL;
    echo "Doctor User: " . ($doctorUser ? "✅ Found (ID: {$doctorUser->id}, Username: {$doctorUser->username})" : "❌ Not found") . PHP_EOL;
    
    // Check clinics
    $clinic = \App\Models\Clinic::first();
    echo "Clinic: " . ($clinic ? "✅ Found (ID: {$clinic->id}, Name: {$clinic->name})" : "❌ Not found") . PHP_EOL;
    
    echo PHP_EOL . "=== Demo Login URLs ===" . PHP_EOL;
    echo "Admin Demo Login: http://127.0.0.1:8001/dev/login-admin" . PHP_EOL;
    echo "Doctor Demo Login: http://127.0.0.1:8001/dev/login-doctor" . PHP_EOL;
    echo "Dashboard Fix: http://127.0.0.1:8001/dev/fix-dashboard" . PHP_EOL;
    echo "Direct Dashboard (bypass middleware): http://127.0.0.1:8001/dev/dashboard" . PHP_EOL;
    
    echo PHP_EOL . "=== Manual Login Credentials ===" . PHP_EOL;
    if ($adminUser) {
        echo "Admin - Username: {$adminUser->username}, Password: admin123" . PHP_EOL;
    }
    if ($doctorUser) {
        echo "Doctor - Username: {$doctorUser->username}, Password: doctor123" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Test Complete ===" . PHP_EOL;
    echo "Try the demo login URLs above to access the dashboard!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
