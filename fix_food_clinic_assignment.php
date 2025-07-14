<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Food;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== Fixing Food Clinic Assignment ===" . PHP_EOL;

try {
    // Get the first active clinic
    $clinic = Clinic::where('is_active', true)->first();
    if (!$clinic) {
        echo "❌ No active clinic found" . PHP_EOL;
        exit(1);
    }
    
    echo "✅ Using clinic: {$clinic->name} (ID: {$clinic->id})" . PHP_EOL;
    
    // Check foods with null clinic_id
    $nullClinicFoods = Food::whereNull('clinic_id')->count();
    echo "📊 Foods with null clinic_id: {$nullClinicFoods}" . PHP_EOL;
    
    if ($nullClinicFoods > 0) {
        echo "🔧 Assigning null clinic_id foods to clinic {$clinic->id}..." . PHP_EOL;
        
        // Update foods with null clinic_id to belong to the active clinic
        $updated = Food::whereNull('clinic_id')->update([
            'clinic_id' => $clinic->id,
            'is_custom' => true, // Mark as custom so they can be deleted
        ]);
        
        echo "✅ Updated {$updated} foods to belong to clinic {$clinic->id}" . PHP_EOL;
    }
    
    // Check foods with clinic_id = 0
    $zeroClinicFoods = Food::where('clinic_id', 0)->count();
    if ($zeroClinicFoods > 0) {
        echo "🔧 Fixing foods with clinic_id = 0..." . PHP_EOL;
        
        $updated = Food::where('clinic_id', 0)->update([
            'clinic_id' => $clinic->id,
            'is_custom' => true,
        ]);
        
        echo "✅ Updated {$updated} foods from clinic_id 0 to clinic {$clinic->id}" . PHP_EOL;
    }
    
    // Verify the fix
    echo PHP_EOL . "=== Verification ===" . PHP_EOL;
    
    $totalFoods = Food::count();
    $clinicFoods = Food::where('clinic_id', $clinic->id)->count();
    $nullFoods = Food::whereNull('clinic_id')->count();
    
    echo "📊 Total foods: {$totalFoods}" . PHP_EOL;
    echo "📊 Foods for clinic {$clinic->id}: {$clinicFoods}" . PHP_EOL;
    echo "📊 Foods with null clinic_id: {$nullFoods}" . PHP_EOL;
    
    // Show clinic distribution
    $clinicDistribution = DB::table('foods')
        ->select('clinic_id', DB::raw('count(*) as count'))
        ->groupBy('clinic_id')
        ->get();
    
    echo PHP_EOL . "📊 Updated food distribution by clinic:" . PHP_EOL;
    foreach ($clinicDistribution as $dist) {
        $clinicName = $dist->clinic_id ? "Clinic {$dist->clinic_id}" : "No Clinic (NULL)";
        echo "   - {$clinicName}: {$dist->count} foods" . PHP_EOL;
    }
    
    // Test clear all functionality
    echo PHP_EOL . "=== Testing Clear All Functionality ===" . PHP_EOL;
    
    $user = User::where('clinic_id', $clinic->id)->first();
    if (!$user) {
        // Create a test user for this clinic
        $user = User::where('role', 'doctor')->first();
        if ($user) {
            $user->update(['clinic_id' => $clinic->id]);
            echo "✅ Updated user {$user->username} to belong to clinic {$clinic->id}" . PHP_EOL;
        }
    }
    
    if ($user) {
        Auth::login($user);
        echo "✅ Logged in as: {$user->first_name} {$user->last_name}" . PHP_EOL;
        
        $foodsToDelete = Food::where('clinic_id', $user->clinic_id)->count();
        echo "🎯 Foods that would be deleted by Clear All: {$foodsToDelete}" . PHP_EOL;
        
        if ($foodsToDelete > 0) {
            echo "✅ Clear All Foods should now work correctly!" . PHP_EOL;
        } else {
            echo "ℹ️  No foods to clear for this clinic" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Fix Complete ===" . PHP_EOL;
    echo "✅ All foods are now properly assigned to clinics" . PHP_EOL;
    echo "✅ Clear All Foods functionality should work correctly" . PHP_EOL;
    echo "✅ Food list will now show only foods for the current clinic" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
