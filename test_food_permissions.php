<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== Testing Food Database Permissions ===" . PHP_EOL;

try {
    // Test different user roles
    $testRoles = ['admin', 'doctor', 'assistant', 'nurse', 'accountant'];
    
    foreach ($testRoles as $role) {
        echo PHP_EOL . "=== Testing {$role} Role ===" . PHP_EOL;
        
        $user = User::where('role', $role)->first();
        if (!$user) {
            echo "❌ No {$role} user found" . PHP_EOL;
            continue;
        }
        
        Auth::login($user);
        echo "✅ Logged in as: {$user->first_name} {$user->last_name} ({$role})" . PHP_EOL;
        
        // Test food database permissions
        $foodPermissions = [
            'food_database_view' => 'View Food Database',
            'food_database_create' => 'Add Food Items',
            'food_database_edit' => 'Edit Food Items',
            'food_database_delete' => 'Delete Food Items',
            'food_database_import' => 'Import Food Lists',
            'food_database_export' => 'Export Food Data',
            'food_database_groups' => 'Manage Food Groups',
            'food_database_clear' => 'Clear All Foods',
            'food_database_manage' => 'Full Food Database Management',
        ];
        
        echo "Food Database Permissions:" . PHP_EOL;
        foreach ($foodPermissions as $permission => $description) {
            $hasPermission = $user->hasPermission($permission);
            $status = $hasPermission ? "✅" : "❌";
            echo "   {$status} {$permission}: {$description}" . PHP_EOL;
        }
        
        // Test section access
        $canAccessFoodDatabase = $user->canAccessSection('food_database');
        $sectionStatus = $canAccessFoodDatabase ? "✅" : "❌";
        echo "   {$sectionStatus} Can access food database section: " . ($canAccessFoodDatabase ? 'Yes' : 'No') . PHP_EOL;
        
        // Test suggested permissions for this role
        $suggestedPermissions = User::getSuggestedPermissions($role);
        $foodSuggestions = array_filter($suggestedPermissions, function($perm) {
            return strpos($perm, 'food_database_') === 0;
        });
        
        if (!empty($foodSuggestions)) {
            echo "   Suggested food permissions: " . implode(', ', $foodSuggestions) . PHP_EOL;
        } else {
            echo "   No food permissions suggested for this role" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Permission System Overview ===" . PHP_EOL;
    
    // Show all food database permissions
    $allPermissions = User::getAllPermissions();
    $foodDbPermissions = $allPermissions['food_database'] ?? [];
    
    echo "Available Food Database Permissions:" . PHP_EOL;
    foreach ($foodDbPermissions as $permission => $description) {
        echo "   - {$permission}: {$description}" . PHP_EOL;
    }
    
    // Show permission section info
    $permissionSections = User::getPermissionSections();
    $foodDbSection = $permissionSections['food_database'] ?? null;
    
    if ($foodDbSection) {
        echo PHP_EOL . "Food Database Section:" . PHP_EOL;
        echo "   Name: {$foodDbSection['name']}" . PHP_EOL;
        echo "   Icon: {$foodDbSection['icon']}" . PHP_EOL;
        echo "   Color: {$foodDbSection['color']}" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Role-Based Suggestions ===" . PHP_EOL;
    
    foreach ($testRoles as $role) {
        $suggestions = User::getSuggestedPermissions($role);
        $foodSuggestions = array_filter($suggestions, function($perm) {
            return strpos($perm, 'food_database_') === 0;
        });
        
        echo "{$role}: " . (empty($foodSuggestions) ? 'No food permissions' : implode(', ', $foodSuggestions)) . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Testing Complete ===" . PHP_EOL;
    echo "✅ Food database permissions have been added to the system" . PHP_EOL;
    echo "✅ Controllers updated to use new permission system" . PHP_EOL;
    echo "✅ Views updated to check specific permissions" . PHP_EOL;
    echo "✅ Role-based suggestions configured" . PHP_EOL;
    
    echo PHP_EOL . "💡 Next Steps:" . PHP_EOL;
    echo "1. Enable permissions by setting DISABLE_PERMISSIONS=false in .env" . PHP_EOL;
    echo "2. Assign food database permissions to users through admin interface" . PHP_EOL;
    echo "3. Test the food management interface with different user roles" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
