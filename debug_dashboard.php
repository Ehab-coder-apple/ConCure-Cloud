<?php
/**
 * Debug ConCure Dashboard Access Issues
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== ConCure Dashboard Debug Tool ===" . PHP_EOL;
echo "Checking why the dashboard page won't open..." . PHP_EOL . PHP_EOL;

// First, let's check what users exist in the database
echo "=== CHECKING AVAILABLE USERS ===" . PHP_EOL;
$users = \App\Models\User::all(['id', 'username', 'email', 'first_name', 'last_name', 'role', 'is_active', 'activated_at', 'clinic_id']);
if ($users->count() > 0) {
    echo "Available users:" . PHP_EOL;
    foreach ($users as $user) {
        $status = $user->is_active ? '✅' : '❌';
        $activated = $user->activated_at ? '✅' : '❌';
        echo "  ID: {$user->id} | {$user->username} ({$user->role}) | Active: {$status} | Activated: {$activated} | Clinic: {$user->clinic_id}" . PHP_EOL;
    }
} else {
    echo "❌ NO USERS FOUND! Database might not be seeded." . PHP_EOL;
    echo "   Solution: Run 'php artisan db:seed' to create demo users" . PHP_EOL;
    exit;
}

echo PHP_EOL;

try {
    // Check if user is logged in
    if (!auth()->check()) {
        echo "❌ ISSUE FOUND: User is not logged in" . PHP_EOL;
        echo "   Solution: Please log in first at /login" . PHP_EOL;
        echo "   Demo credentials:" . PHP_EOL;
        echo "   - Username: admin, Password: admin123" . PHP_EOL;
        echo "   - Username: doctor, Password: doctor123" . PHP_EOL;
        echo "   Or use demo login links:" . PHP_EOL;
        echo "   - Admin: http://127.0.0.1:8001/dev/login-admin" . PHP_EOL;
        echo "   - Doctor: http://127.0.0.1:8001/dev/login-doctor" . PHP_EOL;
        exit;
    }

    $user = auth()->user();
    echo "✅ User is logged in: " . $user->first_name . " " . $user->last_name . PHP_EOL;
    echo "   Role: " . $user->role . PHP_EOL;
    echo "   Email: " . $user->email . PHP_EOL . PHP_EOL;

    // Check user activation
    if (!$user->activated_at) {
        echo "❌ ISSUE FOUND: User account is not activated" . PHP_EOL;
        echo "   activated_at: " . ($user->activated_at ?? 'NULL') . PHP_EOL;
        echo "   Solution: Activate user account" . PHP_EOL;
        
        // Auto-fix: Activate the user
        $user->update(['activated_at' => now()]);
        echo "   ✅ AUTO-FIXED: User account activated" . PHP_EOL . PHP_EOL;
    } else {
        echo "✅ User account is activated" . PHP_EOL;
    }

    // Check if user is active
    if (!$user->is_active) {
        echo "❌ ISSUE FOUND: User account is inactive" . PHP_EOL;
        echo "   is_active: " . ($user->is_active ? 'true' : 'false') . PHP_EOL;
        echo "   Solution: Activate user account" . PHP_EOL;
        
        // Auto-fix: Activate the user
        $user->update(['is_active' => true]);
        echo "   ✅ AUTO-FIXED: User account activated" . PHP_EOL . PHP_EOL;
    } else {
        echo "✅ User account is active" . PHP_EOL;
    }

    // Check clinic status
    if ($user->role !== 'program_owner' && $user->clinic) {
        $clinic = $user->clinic;
        echo "Clinic: " . $clinic->name . PHP_EOL;
        
        if (!$clinic->is_active) {
            echo "❌ ISSUE FOUND: Clinic is inactive" . PHP_EOL;
            echo "   clinic.is_active: " . ($clinic->is_active ? 'true' : 'false') . PHP_EOL;
            echo "   Solution: Activate clinic" . PHP_EOL;
            
            // Auto-fix: Activate the clinic
            $clinic->update(['is_active' => true]);
            echo "   ✅ AUTO-FIXED: Clinic activated" . PHP_EOL . PHP_EOL;
        } else {
            echo "✅ Clinic is active" . PHP_EOL;
        }

        if (!$clinic->activated_at) {
            echo "❌ ISSUE FOUND: Clinic is not activated" . PHP_EOL;
            echo "   clinic.activated_at: " . ($clinic->activated_at ?? 'NULL') . PHP_EOL;
            echo "   Solution: Activate clinic" . PHP_EOL;
            
            // Auto-fix: Activate the clinic
            $clinic->update(['activated_at' => now()]);
            echo "   ✅ AUTO-FIXED: Clinic activated" . PHP_EOL . PHP_EOL;
        } else {
            echo "✅ Clinic is activated" . PHP_EOL;
        }

        // Check subscription
        if ($clinic->subscription_expires_at && $clinic->subscription_expires_at->isPast()) {
            echo "❌ ISSUE FOUND: Clinic subscription expired" . PHP_EOL;
            echo "   subscription_expires_at: " . $clinic->subscription_expires_at . PHP_EOL;
            echo "   Solution: Extend subscription" . PHP_EOL;
            
            // Auto-fix: Extend subscription
            $clinic->update(['subscription_expires_at' => now()->addYear()]);
            echo "   ✅ AUTO-FIXED: Subscription extended for 1 year" . PHP_EOL . PHP_EOL;
        } else {
            echo "✅ Clinic subscription is valid" . PHP_EOL;
        }
    } else if (!$user->clinic) {
        echo "❌ ISSUE FOUND: User has no clinic assigned" . PHP_EOL;
        echo "   Solution: Assign user to a clinic" . PHP_EOL;
        
        // Auto-fix: Create or assign to default clinic
        $defaultClinic = \App\Models\Clinic::first();
        if (!$defaultClinic) {
            $defaultClinic = \App\Models\Clinic::create([
                'name' => 'Default Clinic',
                'email' => 'admin@defaultclinic.com',
                'phone' => '123456789',
                'address' => 'Default Address',
                'is_active' => true,
                'activated_at' => now(),
                'subscription_expires_at' => now()->addYear(),
                'max_users' => 50,
            ]);
            echo "   ✅ AUTO-FIXED: Created default clinic" . PHP_EOL;
        }
        
        $user->update(['clinic_id' => $defaultClinic->id]);
        echo "   ✅ AUTO-FIXED: User assigned to clinic: " . $defaultClinic->name . PHP_EOL . PHP_EOL;
    }

    echo "=== FINAL STATUS ===" . PHP_EOL;
    echo "✅ All checks passed! Dashboard should now be accessible." . PHP_EOL;
    echo "🔗 Try accessing: http://127.0.0.1:8001/dashboard" . PHP_EOL;

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
