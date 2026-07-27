<?php

/**
 * Audit Logging Verification Script
 * 
 * This script verifies that login audit logging is working correctly
 * after the security fix has been deployed.
 * 
 * Usage: php verify_audit_logging.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Audit Logging Verification\n";
echo "========================================\n\n";

// Check 1: Verify LoginController has authenticated method
echo "1. Checking LoginController::authenticated() method...\n";
$loginControllerPath = app_path('Http/Controllers/Auth/LoginController.php');
if (file_exists($loginControllerPath)) {
    $content = file_get_contents($loginControllerPath);
    if (strpos($content, 'protected function authenticated') !== false) {
        echo "   ✅ authenticated() method exists\n";
        if (strpos($content, 'AuditLog::create') !== false) {
            echo "   ✅ Audit log creation code found\n";
        } else {
            echo "   ❌ Audit log creation code NOT found\n";
        }
    } else {
        echo "   ❌ authenticated() method NOT found\n";
    }
} else {
    echo "   ❌ LoginController.php NOT found\n";
}
echo "\n";

// Check 2: Verify backdoor routes are removed
echo "2. Checking for backdoor routes...\n";
$routesPath = base_path('routes/web.php');
if (file_exists($routesPath)) {
    $content = file_get_contents($routesPath);
    $backdoorPatterns = [
        '/login-as/{userId}',
        '/login-as-doctor',
        '/login-as-admin',
        '/dev/login-admin',
        '/dev/login-doctor',
    ];
    
    $foundBackdoors = [];
    foreach ($backdoorPatterns as $pattern) {
        if (strpos($content, $pattern) !== false) {
            $foundBackdoors[] = $pattern;
        }
    }
    
    if (empty($foundBackdoors)) {
        echo "   ✅ No backdoor routes found\n";
    } else {
        echo "   ❌ Found backdoor routes:\n";
        foreach ($foundBackdoors as $route) {
            echo "      - $route\n";
        }
    }
} else {
    echo "   ❌ routes/web.php NOT found\n";
}
echo "\n";

// Check 3: Check recent login audit logs
echo "3. Checking recent login audit logs...\n";
try {
    $recentLogins = \App\Models\AuditLog::where('action', 'login')
        ->orderBy('performed_at', 'desc')
        ->limit(5)
        ->get();
    
    if ($recentLogins->count() > 0) {
        echo "   ✅ Found {$recentLogins->count()} recent login logs\n";
        echo "   Most recent logins:\n";
        foreach ($recentLogins as $log) {
            echo "      - {$log->performed_at} | {$log->user_name} (Clinic {$log->clinic_id})\n";
        }
    } else {
        echo "   ⚠️  No login logs found (this is expected if no one has logged in recently)\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error checking audit logs: " . $e->getMessage() . "\n";
}
echo "\n";

// Check 4: Verify AuditLog model exists
echo "4. Checking AuditLog model...\n";
try {
    $totalAuditLogs = \App\Models\AuditLog::count();
    echo "   ✅ AuditLog model accessible\n";
    echo "   Total audit logs in database: $totalAuditLogs\n";
} catch (\Exception $e) {
    echo "   ❌ Error accessing AuditLog model: " . $e->getMessage() . "\n";
}
echo "\n";

// Check 5: Verify audit logs table structure
echo "5. Checking audit_logs table structure...\n";
try {
    $columns = \DB::select("DESCRIBE audit_logs");
    $requiredColumns = ['user_id', 'user_name', 'action', 'performed_at', 'ip_address'];
    $foundColumns = array_column($columns, 'Field');
    
    $missingColumns = array_diff($requiredColumns, $foundColumns);
    if (empty($missingColumns)) {
        echo "   ✅ All required columns exist\n";
    } else {
        echo "   ❌ Missing columns: " . implode(', ', $missingColumns) . "\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error checking table structure: " . $e->getMessage() . "\n";
}
echo "\n";

// Summary
echo "========================================\n";
echo "Verification Summary\n";
echo "========================================\n";
echo "\n";
echo "✅ = Pass | ❌ = Fail | ⚠️  = Warning\n";
echo "\n";
echo "Next Steps:\n";
echo "1. If all checks pass, the fix is successfully deployed\n";
echo "2. Test login via /login and verify audit log is created\n";
echo "3. Monitor audit logs for the next 24 hours\n";
echo "\n";
echo "To manually test login audit logging:\n";
echo "1. Login via the normal /login form\n";
echo "2. Run: php artisan tinker\n";
echo "3. Execute: App\\Models\\AuditLog::where('action', 'login')->orderBy('performed_at', 'desc')->first();\n";
echo "4. Verify the login was recorded\n";
echo "\n";

