<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== Update User Role to Admin ===" . PHP_EOL;

// Get all users to show current status
$users = User::with('clinic')->get();

echo "Current Users in System:" . PHP_EOL;
echo "------------------------" . PHP_EOL;
foreach ($users as $user) {
    $clinicName = $user->clinic ? $user->clinic->name : 'No Clinic';
    echo "ID: {$user->id} | {$user->first_name} {$user->last_name} | Role: {$user->role} | Email: {$user->email} | Clinic: {$clinicName}" . PHP_EOL;
}

echo PHP_EOL . "Which user would you like to make Admin?" . PHP_EOL;
echo "Enter the user ID: ";

// For automation, let's find the doctor user and update them
$doctorUser = User::where('role', 'doctor')->first();

if ($doctorUser) {
    echo PHP_EOL . "Found Doctor user: {$doctorUser->first_name} {$doctorUser->last_name} (ID: {$doctorUser->id})" . PHP_EOL;
    
    // Update the user role to admin
    $doctorUser->update([
        'role' => 'admin',
        'permissions' => [
            // Give full admin permissions
            'dashboard_view', 'dashboard_stats',
            'patients_view', 'patients_create', 'patients_edit', 'patients_delete', 'patients_files', 'patients_history',
            'prescriptions_view', 'prescriptions_create', 'prescriptions_edit', 'prescriptions_delete', 'prescriptions_print',
            'appointments_view', 'appointments_create', 'appointments_edit', 'appointments_delete', 'appointments_manage',
            'medicines_view', 'medicines_create', 'medicines_edit', 'medicines_delete', 'medicines_inventory',
            'nutrition_view', 'nutrition_create', 'nutrition_edit', 'nutrition_delete', 'nutrition_manage',
            'lab_requests_view', 'lab_requests_create', 'lab_requests_edit', 'lab_requests_delete',
            'users_view', 'users_create', 'users_edit', 'users_delete', 'users_permissions',
            'settings_view', 'settings_edit',
            'reports_view', 'reports_generate', 'reports_export',
            'finance_view', 'finance_create', 'finance_edit', 'finance_reports',
            'audit_view', 'audit_export',
        ]
    ]);
    
    echo "✅ Successfully updated {$doctorUser->first_name} {$doctorUser->last_name} to Admin role!" . PHP_EOL;
    echo "   - Role changed from 'doctor' to 'admin'" . PHP_EOL;
    echo "   - Full admin permissions granted" . PHP_EOL;
    
    // Show updated user info
    $updatedUser = User::find($doctorUser->id);
    echo PHP_EOL . "Updated User Info:" . PHP_EOL;
    echo "- Name: {$updatedUser->first_name} {$updatedUser->last_name}" . PHP_EOL;
    echo "- Email: {$updatedUser->email}" . PHP_EOL;
    echo "- Role: {$updatedUser->role}" . PHP_EOL;
    echo "- Role Display: {$updatedUser->role_display}" . PHP_EOL;
    echo "- Permissions: " . count($updatedUser->permissions ?? []) . " permissions granted" . PHP_EOL;
    
} else {
    echo "❌ No doctor user found in the system." . PHP_EOL;
    echo "Available users:" . PHP_EOL;
    foreach ($users as $user) {
        echo "- {$user->first_name} {$user->last_name} ({$user->role})" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Update Complete ===" . PHP_EOL;
echo "Please refresh your browser to see the changes." . PHP_EOL;
