<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodGroup;
use App\Models\Food;
use Illuminate\Support\Facades\DB;

echo "=== Testing Food Group Colors ===" . PHP_EOL;

try {
    // Check all food groups and their colors
    $foodGroups = FoodGroup::all();
    echo "📊 Found {$foodGroups->count()} food groups" . PHP_EOL;
    
    echo PHP_EOL . "=== Food Group Color Analysis ===" . PHP_EOL;
    
    $problematicGroups = [];
    
    foreach ($foodGroups as $group) {
        $color = $group->color;
        $status = "✅";
        $issue = "";
        
        // Check for problematic colors
        if (empty($color)) {
            $status = "❌";
            $issue = "No color assigned";
            $problematicGroups[] = $group;
        } elseif ($color === '#ffffff' || $color === 'white') {
            $status = "⚠️ ";
            $issue = "White background (invisible text)";
            $problematicGroups[] = $group;
        } elseif ($color === '#000000' || $color === 'black') {
            $status = "⚠️ ";
            $issue = "Black background (may be hard to read)";
        } elseif (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $status = "❌";
            $issue = "Invalid color format";
            $problematicGroups[] = $group;
        }
        
        $colorDisplay = $color ?: 'null';
        echo "{$status} {$group->name}: {$colorDisplay}";
        if ($issue) {
            echo " ({$issue})";
        }
        echo PHP_EOL;
    }
    
    echo PHP_EOL . "=== Summary ===" . PHP_EOL;
    echo "📊 Total food groups: {$foodGroups->count()}" . PHP_EOL;
    echo "⚠️  Problematic groups: " . count($problematicGroups) . PHP_EOL;
    
    if (count($problematicGroups) > 0) {
        echo PHP_EOL . "🔧 Groups that need fixing:" . PHP_EOL;
        foreach ($problematicGroups as $group) {
            echo "   - {$group->name} (ID: {$group->id})" . PHP_EOL;
        }
        
        echo PHP_EOL . "💡 Run 'php fix_food_group_colors.php' to fix these issues" . PHP_EOL;
    } else {
        echo "✅ All food groups have proper colors!" . PHP_EOL;
    }
    
    // Check if there are foods with the problematic groups
    if (count($problematicGroups) > 0) {
        echo PHP_EOL . "=== Foods Affected ===" . PHP_EOL;
        
        foreach ($problematicGroups as $group) {
            $foodCount = Food::where('food_group_id', $group->id)->count();
            if ($foodCount > 0) {
                echo "⚠️  {$group->name}: {$foodCount} foods affected" . PHP_EOL;
                
                // Show sample foods
                $sampleFoods = Food::where('food_group_id', $group->id)
                    ->limit(3)
                    ->pluck('name')
                    ->toArray();
                
                if (!empty($sampleFoods)) {
                    echo "   Sample foods: " . implode(', ', $sampleFoods) . PHP_EOL;
                }
            }
        }
    }
    
    // Test the view logic
    echo PHP_EOL . "=== Testing View Logic ===" . PHP_EOL;
    
    $testGroup = FoodGroup::first();
    if ($testGroup) {
        echo "🧪 Testing with group: {$testGroup->name}" . PHP_EOL;
        
        // Simulate the view logic
        $bgColor = $testGroup->color ?? '#6c757d';
        if (empty($bgColor) || $bgColor === '#ffffff' || $bgColor === 'white') {
            $bgColor = '#6c757d';
        }
        
        $textColor = 'white';
        if ($bgColor === '#FFEB3B' || $bgColor === '#FFC107' || $bgColor === '#FFFF00') {
            $textColor = '#333';
        }
        
        echo "   Original color: " . ($testGroup->color ?: 'null') . PHP_EOL;
        echo "   Final background: {$bgColor}" . PHP_EOL;
        echo "   Text color: {$textColor}" . PHP_EOL;
        echo "   Badge HTML: <span style=\"background-color: {$bgColor}; color: {$textColor};\">{$testGroup->translated_name}</span>" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== Recommendations ===" . PHP_EOL;
    
    if (count($problematicGroups) > 0) {
        echo "🔧 Run the color fix script: php fix_food_group_colors.php" . PHP_EOL;
        echo "🎨 The view now has fallback colors for problematic cases" . PHP_EOL;
        echo "✅ CSS rules will ensure badges are always visible" . PHP_EOL;
    } else {
        echo "✅ All food group colors are properly configured" . PHP_EOL;
        echo "✅ Category badges should be clearly visible" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
