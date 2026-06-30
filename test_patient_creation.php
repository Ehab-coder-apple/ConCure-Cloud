<?php
/**
 * Test patient creation for any clinic
 * Run: php test_patient_creation.php [clinic_id]
 * Example: php test_patient_creation.php 25
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Clinic;
use App\Models\User;
use App\Models\Patient;
use App\Models\PatientMedicalOverview;
use Illuminate\Support\Facades\DB;

// Get clinic ID from command line argument or default to 45
$clinicId = isset($argv[1]) ? (int)$argv[1] : 45;

echo "=== Testing Patient Creation for Clinic ID {$clinicId} ===\n\n";

// Find clinic
$clinic = Clinic::find($clinicId);
if (!$clinic) {
    echo "❌ Clinic ID {$clinicId} not found\n";
    echo "\nAvailable clinics:\n";
    foreach (Clinic::all() as $c) {
        echo "  - ID: {$c->id}, Name: {$c->name}\n";
    }
    exit(1);
}

echo "✅ Clinic: {$clinic->name}\n";
echo "   - Is Active: " . ($clinic->is_active ? 'Yes' : 'No') . "\n";
echo "   - Activated: " . ($clinic->activated ? 'Yes' : 'No') . "\n\n";

// Get a user from this clinic
$user = User::where('clinic_id', $clinicId)->first();
if (!$user) {
    echo "❌ No users found in clinic {$clinicId}\n";
    exit(1);
}

echo "✅ User: {$user->first_name} {$user->last_name} ({$user->role})\n\n";

// TEST 1: Check existing patients with this clinic's prefix
echo "TEST 1: Checking existing patient IDs...\n";
$prefix = strtoupper(substr($clinic->name, 0, 3));
$prefix = trim($prefix);
if ($prefix === '') {
    $prefix = 'CL' . $clinicId;
}
echo "   Clinic prefix: {$prefix}\n";

$lastPatient = Patient::where('clinic_id', $clinicId)
    ->where('patient_id', 'LIKE', $prefix . '-%')
    ->orderByRaw('CAST(SUBSTRING_INDEX(patient_id, "-", -1) AS UNSIGNED) DESC')
    ->first();

if ($lastPatient) {
    echo "   Last patient ID: {$lastPatient->patient_id}\n";
} else {
    echo "   No existing patients with this prefix\n";
}
echo "\n";

// TEST 2: Creating TWO patients in a row (simulating real-world scenario)
echo "TEST 2: Creating TWO patients in a row (like browser submission)...\n";
try {
    DB::beginTransaction();
    
    // First patient
    $patient1 = Patient::create([
        'first_name' => 'Test',
        'last_name' => 'Patient_' . time(),
        'phone' => '07701111111',
        'clinic_id' => $clinicId,
        'created_by' => $user->id,
        'is_active' => true,
    ]);
    echo "✅ First patient created: {$patient1->patient_id}\n";
    
    // Second patient immediately after (this is where the error happens)
    $patient2 = Patient::create([
        'first_name' => 'Second',
        'last_name' => 'Patient_' . (time() + 1),
        'phone' => '07702222222',
        'clinic_id' => $clinicId,
        'created_by' => $user->id,
        'is_active' => true,
    ]);
    echo "✅ Second patient created: {$patient2->patient_id}\n";
    
    // Third patient to really test sequential generation
    $patient3 = Patient::create([
        'first_name' => 'Third',
        'last_name' => 'Patient_' . (time() + 2),
        'phone' => '07703333333',
        'clinic_id' => $clinicId,
        'created_by' => $user->id,
        'is_active' => true,
    ]);
    echo "✅ Third patient created: {$patient3->patient_id}\n";
    
    DB::rollBack();
    echo "✅ All three patients created successfully! (rolled back)\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ FAILED!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    // Check if it's a duplicate entry error
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo "\n⚠️  This is a DUPLICATE PATIENT ID error - the race condition fix may not be working!\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "\nNext steps:\n";
echo "1. If TEST 2 failed, check the error above\n";
echo "2. Try creating a patient in the browser at clinic {$clinicId}\n";
echo "3. Check Laravel logs: tail -100 storage/logs/laravel.log\n";
