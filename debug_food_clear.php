<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Food;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== Debugging Food Clear Issue ===" . PHP_EOL;

try {
    // Login as a user
    $user = User::where('role', 'doctor')->first();
    if (!$user) {
        echo "❌ No doctor user found" . PHP_EOL;
        exit(1);
    }
    
    Auth::login($user);
    echo "✅ Logged in as: {$user->first_name} {$user->last_name}" . PHP_EOL;
    echo "✅ User ID: {$user->id}" . PHP_EOL;
    echo "✅ Clinic ID: {$user->clinic_id}" . PHP_EOL;
    
    echo PHP_EOL . "=== Food Database Analysis ===" . PHP_EOL;
    
    // Check total foods in database
    $totalFoods = Food::count();
    echo "📊 Total foods in database: {$totalFoods}" . PHP_EOL;
    
    // Check foods by clinic
    $clinicFoods = Food::where('clinic_id', $user->clinic_id)->count();
    echo "📊 Foods for clinic {$user->clinic_id}: {$clinicFoods}" . PHP_EOL;
    
    // Check foods with null clinic_id
    $nullClinicFoods = Food::whereNull('clinic_id')->count();
    echo "📊 Foods with null clinic_id: {$nullClinicFoods}" . PHP_EOL;
    
    // Check foods with different clinic_ids
    $clinicDistribution = DB::table('foods')
        ->select('clinic_id', DB::raw('count(*) as count'))
        ->groupBy('clinic_id')
        ->get();
    
    echo PHP_EOL . "📊 Food distribution by clinic:" . PHP_EOL;
    foreach ($clinicDistribution as $dist) {
        $clinicName = $dist->clinic_id ? "Clinic {$dist->clinic_id}" : "No Clinic (NULL)";
        echo "   - {$clinicName}: {$dist->count} foods" . PHP_EOL;
    }
    
    // Show sample foods
    echo PHP_EOL . "📋 Sample foods (first 10):" . PHP_EOL;
    $sampleFoods = Food::select('id', 'name', 'clinic_id', 'is_custom', 'created_by')
        ->limit(10)
        ->get();
    
    foreach ($sampleFoods as $food) {
        $clinicInfo = $food->clinic_id ? "Clinic {$food->clinic_id}" : "No Clinic";
        $typeInfo = $food->is_custom ? "Custom" : "Standard";
        $creatorInfo = $food->created_by ? "User {$food->created_by}" : "System";
        echo "   - ID: {$food->id} | {$food->name} | {$clinicInfo} | {$typeInfo} | {$creatorInfo}" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Testing Clear Logic ===" . PHP_EOL;
    
    // Test what would be deleted
    $toDelete = Food::where('clinic_id', $user->clinic_id)->get();
    echo "🎯 Foods that would be deleted: {$toDelete->count()}" . PHP_EOL;
    
    if ($toDelete->count() > 0) {
        echo "   Foods to delete:" . PHP_EOL;
        foreach ($toDelete->take(5) as $food) {
            echo "   - {$food->name} (ID: {$food->id})" . PHP_EOL;
        }
        if ($toDelete->count() > 5) {
            echo "   - ... and " . ($toDelete->count() - 5) . " more" . PHP_EOL;
        }
    }
    
    // Test what would remain
    $toRemain = Food::where('clinic_id', '!=', $user->clinic_id)
        ->orWhereNull('clinic_id')
        ->get();
    echo "🎯 Foods that would remain: {$toRemain->count()}" . PHP_EOL;
    
    if ($toRemain->count() > 0) {
        echo "   Foods that would remain:" . PHP_EOL;
        foreach ($toRemain->take(5) as $food) {
            $clinicInfo = $food->clinic_id ? "Clinic {$food->clinic_id}" : "No Clinic";
            echo "   - {$food->name} ({$clinicInfo})" . PHP_EOL;
        }
        if ($toRemain->count() > 5) {
            echo "   - ... and " . ($toRemain->count() - 5) . " more" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
    
    if ($clinicFoods === 0 && $nullClinicFoods > 0) {
        echo "⚠️  Issue: All foods have null clinic_id" . PHP_EOL;
        echo "💡 Solution: Foods need to be assigned to clinics during import" . PHP_EOL;
    } elseif ($clinicFoods > 0) {
        echo "✅ Foods are properly assigned to clinic {$user->clinic_id}" . PHP_EOL;
        echo "💡 Clear All should work correctly" . PHP_EOL;
    } else {
        echo "ℹ️  No foods found for current clinic" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
