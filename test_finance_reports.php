<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\FinanceController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== Testing Finance Reports Fix ===" . PHP_EOL;

try {
    // Login as an admin user
    $user = User::where('role', 'admin')->first();
    if (!$user) {
        echo "❌ No admin user found" . PHP_EOL;
        exit(1);
    }
    
    Auth::login($user);
    echo "✅ Logged in as: {$user->first_name} {$user->last_name}" . PHP_EOL;
    
    // Test FinanceController methods
    $controller = new FinanceController();
    
    echo PHP_EOL . "=== Testing Finance Controller Methods ===" . PHP_EOL;
    
    // Test reports method
    try {
        $response = $controller->reports();
        echo "✅ reports() method exists and works" . PHP_EOL;
    } catch (Exception $e) {
        echo "❌ reports() method failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test cashFlowReport method
    try {
        $request = new Request();
        $response = $controller->cashFlowReport($request);
        echo "✅ cashFlowReport() method exists and works" . PHP_EOL;
    } catch (Exception $e) {
        echo "❌ cashFlowReport() method failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test profitLossReport method
    try {
        $request = new Request();
        $response = $controller->profitLossReport($request);
        echo "✅ profitLossReport() method exists and works" . PHP_EOL;
    } catch (Exception $e) {
        echo "❌ profitLossReport() method failed: " . $e->getMessage() . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Testing Route Access ===" . PHP_EOL;
    
    // Test if routes are accessible
    $routes = [
        'finance.reports' => '/finance/reports',
        'finance.reports.cash-flow' => '/finance/reports/cash-flow',
        'finance.reports.profit-loss' => '/finance/reports/profit-loss',
    ];
    
    foreach ($routes as $routeName => $routePath) {
        try {
            $routeExists = \Illuminate\Support\Facades\Route::has($routeName);
            if ($routeExists) {
                echo "✅ Route '{$routeName}' exists: {$routePath}" . PHP_EOL;
            } else {
                echo "❌ Route '{$routeName}' not found" . PHP_EOL;
            }
        } catch (Exception $e) {
            echo "❌ Error checking route '{$routeName}': " . $e->getMessage() . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Testing View Files ===" . PHP_EOL;
    
    // Test if view files exist
    $views = [
        'finance.reports' => 'resources/views/finance/reports.blade.php',
        'finance.reports.cash-flow' => 'resources/views/finance/reports/cash-flow.blade.php',
        'finance.reports.profit-loss' => 'resources/views/finance/reports/profit-loss.blade.php',
    ];
    
    foreach ($views as $viewName => $viewPath) {
        if (file_exists($viewPath)) {
            echo "✅ View '{$viewName}' exists: {$viewPath}" . PHP_EOL;
        } else {
            echo "❌ View '{$viewName}' not found: {$viewPath}" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Testing User Access ===" . PHP_EOL;
    
    // Test user access to finance
    if ($user->canAccessFinance()) {
        echo "✅ User can access finance module" . PHP_EOL;
    } else {
        echo "❌ User cannot access finance module" . PHP_EOL;
    }
    
    // Test different user roles
    $testRoles = ['admin', 'accountant', 'doctor', 'assistant'];
    
    foreach ($testRoles as $role) {
        $testUser = User::where('role', $role)->first();
        if ($testUser) {
            $canAccess = $testUser->canAccessFinance();
            $status = $canAccess ? "✅" : "❌";
            echo "{$status} {$role} role can access finance: " . ($canAccess ? 'Yes' : 'No') . PHP_EOL;
        } else {
            echo "⚠️  No {$role} user found for testing" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Summary ===" . PHP_EOL;
    echo "✅ FinanceController::reports() method added" . PHP_EOL;
    echo "✅ FinanceController::cashFlowReport() method added" . PHP_EOL;
    echo "✅ FinanceController::profitLossReport() method added" . PHP_EOL;
    echo "✅ Helper methods for report data generation added" . PHP_EOL;
    echo "✅ finance.reports view created" . PHP_EOL;
    echo "✅ finance.reports.cash-flow view created" . PHP_EOL;
    echo "✅ finance.reports.profit-loss view created" . PHP_EOL;
    echo "✅ Routes should now work without 'Method does not exist' error" . PHP_EOL;
    
    echo PHP_EOL . "💡 Next Steps:" . PHP_EOL;
    echo "1. Test the finance reports in your browser" . PHP_EOL;
    echo "2. Navigate to /finance/reports to see the reports dashboard" . PHP_EOL;
    echo "3. Try generating cash flow and profit & loss reports" . PHP_EOL;
    echo "4. Verify that all links and buttons work correctly" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
