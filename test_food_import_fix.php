<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Imports\FoodsImport;
use App\Models\User;
use App\Models\FoodGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

echo "=== Testing Food Import Fix ===" . PHP_EOL;

try {
    // Login as a user
    $user = User::where('role', 'doctor')->first();
    if (!$user) {
        echo "❌ No doctor user found" . PHP_EOL;
        exit(1);
    }
    
    Auth::login($user);
    echo "✅ Logged in as: {$user->first_name} {$user->last_name}" . PHP_EOL;
    
    // Create some test food groups if they don't exist
    $groups = ['Proteins', 'Vegetables', 'Fruits', 'Grains', 'Dairy'];
    foreach ($groups as $groupName) {
        $group = FoodGroup::where('name', $groupName)->first();
        if (!$group) {
            FoodGroup::create([
                'name' => $groupName,
                'name_translations' => [
                    'en' => $groupName,
                    'ar' => $groupName,
                    'ku' => $groupName
                ],
                'description' => "Food group: {$groupName}",
                'is_active' => true,
                'sort_order' => 1
            ]);
            echo "✅ Created food group: {$groupName}" . PHP_EOL;
        }
    }
    
    // Test data that was causing errors
    $testData = collect([
        [
            'name' => 'Chicken Breast (Skinless)',
            'name_en' => 'Chicken Breast (Skinless)',
            'name_ar' => 'صدر دجاج (بدون جلد)',
            'name_ku_bahdini' => 'سنگی مریشک (بێ پێست)',
            'name_ku_sorani' => 'سنگی مریشک (بێ پێست)',
            'food_group' => 'Proteins',
            'calories' => 165,
            'protein' => 31,
            'carbohydrates' => 0,
            'fat' => 3.6,
            'fiber' => 0,
            'sugar' => 0,
            'sodium' => 74,
            'serving_size' => '100g',
            'serving_weight' => 100,
            'description' => 'Lean protein source',
        ],
        [
            'name' => 'Brown Rice (Cooked)',
            'name_en' => 'Brown Rice (Cooked)',
            'name_ar' => 'أرز بني (مطبوخ)',
            'name_ku_bahdini' => 'برنجی قاوەیی (کراو)',
            'name_ku_sorani' => 'برنجی قاوەیی (کراو)',
            'food_group' => 'Grains',
            'calories' => 112,
            'protein' => 2.6,
            'carbohydrates' => 23,
            'fat' => 0.9,
            'fiber' => 1.8,
            'sugar' => 0.4,
            'sodium' => 5,
            'serving_size' => '1 cup',
            'serving_weight' => 195,
            'description' => 'Whole grain rice',
        ],
        [
            'name' => 'Broccoli (Raw)',
            'name_en' => 'Broccoli (Raw)',
            'name_ar' => 'بروكلي (نيء)',
            'name_ku_bahdini' => 'بڕۆکلی (خاو)',
            'name_ku_sorani' => 'بڕۆکلی (خاو)',
            'food_group' => 'Vegetables',
            'calories' => 34,
            'protein' => 2.8,
            'carbohydrates' => 7,
            'fat' => 0.4,
            'fiber' => 2.6,
            'sugar' => 1.5,
            'sodium' => 33,
            'serving_size' => '1 cup',
            'serving_weight' => 91,
            'description' => 'Nutritious green vegetable',
        ],
        [
            'name' => 'Bread',
            'name_en' => 'Bread',
            'name_ar' => 'خبز',
            'name_ku_bahdini' => 'نان',
            'name_ku_sorani' => 'نان',
            'food_group' => 'Grains',
            'calories' => 265,
            'protein' => 9,
            'carbohydrates' => 49,
            'fat' => 3.2,
            'fiber' => 2.7,
            'sugar' => 5,
            'sodium' => 491,
            'serving_size' => '1 slice',
            'serving_weight' => 30,
            'description' => 'Basic bread slice',
        ]
    ]);
    
    echo PHP_EOL . "Testing import with " . $testData->count() . " foods..." . PHP_EOL;
    
    // Test the import
    $import = new FoodsImport();
    $import->collection($testData);
    
    echo PHP_EOL . "=== Import Results ===" . PHP_EOL;
    echo "✅ Imported: " . $import->getImportedCount() . PHP_EOL;
    echo "⚠️  Skipped: " . $import->getSkippedCount() . PHP_EOL;
    echo "❌ Errors: " . count($import->getErrors()) . PHP_EOL;
    
    if (!empty($import->getErrors())) {
        echo PHP_EOL . "Error details:" . PHP_EOL;
        foreach ($import->getErrors() as $error) {
            echo "- " . $error . PHP_EOL;
        }
    } else {
        echo "✅ No errors! Import successful!" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
