<?php
/**
 * Test patient creation for Clinic ID 45 (Dr Abdul Rahman)
 * Run: php test_clinic_45.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Clinic;
use App\Models\User;
use App\Models\Patient;
use App\Models\PatientMedicalOverview;
use App\Services\PatientProfileModuleRegistry;

echo "=== Testing Clinic ID 45 Patient Creation ===\n\n";

// Get clinic 45
$clinic = Clinic::find(45);
if (!$clinic) {
    die("❌ Clinic 45 not found\n");
}

echo "✅ Clinic: {$clinic->name}\n";
echo "   - Is Active: " . ($clinic->is_active ? 'Yes' : 'No') . "\n";
echo "   - Activated: " . ($clinic->activated_at ? 'Yes' : 'No') . "\n\n";

// Get a user from this clinic
$user = User::where('clinic_id', 45)->first();
if (!$user) {
    die("❌ No users found in clinic 45\n");
}

echo "✅ User: {$user->first_name} {$user->last_name} ({$user->role})\n\n";

// Test 1: Check enabled modules
echo "TEST 1: Checking enabled modules...\n";
try {
    $enabledModules = PatientProfileModuleRegistry::modulesForClinic($clinic);
    echo "✅ Found " . count($enabledModules) . " enabled modules\n";
    foreach ($enabledModules as $module) {
        echo "   - {$module['key']}: {$module['label']}\n";
    }
} catch (\Exception $e) {
    echo "❌ Failed to get enabled modules: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}
echo "\n";

// Test 2: Try to create a minimal patient
echo "TEST 2: Creating minimal patient (required fields only)...\n";
try {
    \DB::beginTransaction();
    
    $patient = Patient::create([
        'first_name' => 'Test',
        'last_name' => 'Minimal_' . time(),
        'phone' => '1234567890',
        'clinic_id' => 45,
        'created_by' => $user->id,
        'is_active' => true,
    ]);
    
    echo "✅ Patient created! ID: {$patient->id}, Patient ID: {$patient->patient_id}\n";
    
    // Test creating medical overview
    echo "   Creating medical overview...\n";
    PatientMedicalOverview::create([
        'patient_id' => $patient->id,
        'flags' => [],
    ]);
    echo "✅ Medical overview created\n";
    
    \DB::rollBack();
    echo "✅ Test successful (rolled back)\n";
    
} catch (\Exception $e) {
    \DB::rollBack();
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   SQL State: " . ($e->getCode() ?? 'N/A') . "\n";
    if (method_exists($e, 'getSql')) {
        echo "   SQL: " . $e->getSql() . "\n";
    }
    echo "\n   Full Trace:\n" . $e->getTraceAsString() . "\n";
}
echo "\n";

// Test 3: Create patient with date_of_birth and gender
echo "TEST 3: Creating patient with date_of_birth and gender...\n";
try {
    \DB::beginTransaction();
    
    $patient = Patient::create([
        'first_name' => 'Test',
        'last_name' => 'Complete_' . time(),
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'phone' => '1234567890',
        'clinic_id' => 45,
        'created_by' => $user->id,
        'is_active' => true,
    ]);
    
    echo "✅ Patient with DOB/gender created successfully!\n";
    \DB::rollBack();
    
} catch (\Exception $e) {
    \DB::rollBack();
    echo "❌ FAILED: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Create patient WITHOUT date_of_birth and gender
echo "TEST 4: Creating patient WITHOUT date_of_birth and gender...\n";
try {
    \DB::beginTransaction();
    
    $patient = Patient::create([
        'first_name' => 'Test',
        'last_name' => 'NoDOB_' . time(),
        'phone' => '1234567890',
        'clinic_id' => 45,
        'created_by' => $user->id,
        'is_active' => true,
    ]);
    
    echo "✅ Patient WITHOUT DOB/gender created successfully!\n";
    \DB::rollBack();
    
} catch (\Exception $e) {
    \DB::rollBack();
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    echo "   This means the migration didn't run!\n";
}
echo "\n";

echo "=== Test Complete ===\n";
echo "\nNext steps:\n";
echo "1. If TEST 4 failed, run: php artisan migrate\n";
echo "2. Check Laravel logs: tail -50 storage/logs/laravel.log\n";
