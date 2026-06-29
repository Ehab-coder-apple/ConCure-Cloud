<?php
/**
 * Diagnostic script for patient creation errors
 * Upload this to the server and run: php debug_patient_creation.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Clinic;
use App\Models\User;
use App\Models\Patient;

echo "=== Patient Creation Diagnostic Tool ===\n\n";

// Find Dr. AbdulRahman's clinic (ID 45)
$clinic = Clinic::find(45);

if (!$clinic) {
    echo "❌ Could not find clinic with ID 45\n";
    echo "Searching by name...\n";
    $clinic = Clinic::where('name', 'LIKE', '%Abdul Rahman%')
        ->orWhere('name', 'LIKE', '%عبدالرحمن%')
        ->first();

    if (!$clinic) {
        echo "❌ Could not find Dr. AbdulRahman's clinic\n";
        echo "Available clinics:\n";
        foreach (Clinic::all() as $c) {
            echo "  - ID: {$c->id}, Name: {$c->name}\n";
        }
        exit(1);
    }
}

echo "✅ Found clinic: {$clinic->name} (ID: {$clinic->id})\n\n";

// Check clinic settings
echo "Clinic Settings:\n";
echo "  - Is Active: " . ($clinic->is_active ? 'Yes' : 'No') . "\n";
echo "  - Is Demo: " . ($clinic->is_demo ? 'Yes' : 'No') . "\n";
echo "  - Activated At: " . ($clinic->activated_at ? $clinic->activated_at->format('Y-m-d H:i:s') : 'Not activated') . "\n";
echo "  - Storage Used: " . number_format($clinic->storage_used ?? 0) . " bytes\n";
echo "  - Storage Limit: " . number_format($clinic->storage_limit ?? 5368709120) . " bytes\n";
echo "\n";

// Check users
$users = User::where('clinic_id', $clinic->id)->get();
echo "Users in clinic: " . $users->count() . "\n";
foreach ($users->take(3) as $user) {
    echo "  - {$user->first_name} {$user->last_name} ({$user->role})\n";
}
echo "\n";

// Check existing patients
$patientCount = Patient::where('clinic_id', $clinic->id)->count();
echo "Existing patients: {$patientCount}\n\n";

// Test patient ID generation
echo "Testing Patient ID Generation:\n";
try {
    for ($i = 1; $i <= 5; $i++) {
        $patientId = Patient::generatePatientId($clinic->id);
        echo "  Attempt {$i}: {$patientId}\n";
    }
    echo "✅ Patient ID generation working\n\n";
} catch (\Exception $e) {
    echo "❌ Patient ID generation failed: " . $e->getMessage() . "\n\n";
}

// Test creating a patient
echo "Testing Patient Creation:\n";
$testUser = $users->first();
if (!$testUser) {
    echo "❌ No users found in clinic\n";
    exit(1);
}

try {
    $testPatient = Patient::create([
        'first_name' => 'Test',
        'last_name' => 'Patient_' . time(),
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'phone' => '1234567890',
        'clinic_id' => $clinic->id,
        'created_by' => $testUser->id,
        'is_active' => true,
    ]);
    
    echo "✅ Test patient created successfully!\n";
    echo "  - Patient ID: {$testPatient->patient_id}\n";
    echo "  - Name: {$testPatient->first_name} {$testPatient->last_name}\n";
    
    // Clean up
    $testPatient->delete();
    echo "  - Test patient cleaned up\n";
    
} catch (\Exception $e) {
    echo "❌ Patient creation failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Check Laravel Logs ===\n";
echo "Run: tail -100 storage/logs/laravel.log\n";
echo "\n=== Diagnostic Complete ===\n";
