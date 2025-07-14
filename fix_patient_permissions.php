<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Patient;
use App\Models\Clinic;

echo "=== Fixing Patient Access Permissions ===" . PHP_EOL;

// Get all users
$users = User::all();
echo "Found " . $users->count() . " users" . PHP_EOL;

foreach ($users as $user) {
    echo PHP_EOL . "User: " . $user->first_name . " " . $user->last_name . " (Role: " . $user->role . ")" . PHP_EOL;
    echo "Current permissions: " . json_encode($user->permissions) . PHP_EOL;
    
    // Get suggested permissions for the user's role
    $suggestedPermissions = User::getSuggestedPermissions($user->role);
    
    // Update user permissions if they don't have any
    if (empty($user->permissions) || !$user->hasPermission('patients_view')) {
        $user->permissions = $suggestedPermissions;
        $user->save();
        echo "✅ Updated permissions for " . $user->first_name . " " . $user->last_name . PHP_EOL;
        echo "New permissions: " . json_encode($user->permissions) . PHP_EOL;
    } else {
        echo "✓ User already has permissions" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Checking Patient-Clinic Relationships ===" . PHP_EOL;

$patients = Patient::with('clinic')->take(5)->get();
foreach ($patients as $patient) {
    echo "Patient: " . $patient->first_name . " " . $patient->last_name . PHP_EOL;
    echo "  Clinic ID: " . $patient->clinic_id . PHP_EOL;
    echo "  Clinic Name: " . ($patient->clinic ? $patient->clinic->name : 'No clinic found') . PHP_EOL;
}

echo PHP_EOL . "=== Checking User-Clinic Relationships ===" . PHP_EOL;

foreach ($users as $user) {
    echo "User: " . $user->first_name . " " . $user->last_name . PHP_EOL;
    echo "  Clinic ID: " . $user->clinic_id . PHP_EOL;
    echo "  Clinic Name: " . ($user->clinic ? $user->clinic->name : 'No clinic found') . PHP_EOL;
}

echo PHP_EOL . "=== Permission Fix Complete ===" . PHP_EOL;
echo "Try accessing the patient page again!" . PHP_EOL;
