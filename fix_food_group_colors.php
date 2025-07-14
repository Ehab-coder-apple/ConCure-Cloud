<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FoodGroup;
use Illuminate\Support\Facades\DB;

echo "=== Fixing Food Group Colors ===" . PHP_EOL;

try {
    // Define default colors for common food groups
    $defaultColors = [
        'vegetables' => '#4CAF50',    // Green
        'fruits' => '#FF9800',        // Orange
        'grains' => '#8BC34A',        // Light Green
        'proteins' => '#F44336',      // Red
        'dairy' => '#2196F3',         // Blue
        'fats' => '#FFEB3B',          // Yellow
        'oils' => '#FFEB3B',          // Yellow
        'sweets' => '#E91E63',        // Pink
        'desserts' => '#E91E63',      // Pink
        'beverages' => '#00BCD4',     // Cyan
        'drinks' => '#00BCD4',        // Cyan
        'snacks' => '#FF5722',        // Deep Orange
        'nuts' => '#795548',          // Brown
        'seeds' => '#795548',         // Brown
        'legumes' => '#689F38',       // Olive Green
        'beans' => '#689F38',         // Olive Green
        'spices' => '#9C27B0',        // Purple
        'herbs' => '#4CAF50',         // Green
        'condiments' => '#607D8B',    // Blue Grey
        'sauces' => '#607D8B',        // Blue Grey
        'general' => '#6c757d',       // Gray
        'other' => '#6c757d',         // Gray
        'miscellaneous' => '#6c757d', // Gray
    ];
    
    // Get all food groups
    $foodGroups = FoodGroup::all();
    echo "📊 Found {$foodGroups->count()} food groups" . PHP_EOL;
    
    $fixedCount = 0;
    
    foreach ($foodGroups as $group) {
        $needsFix = false;
        $newColor = null;
        
        // Check if color is missing, empty, or problematic
        if (empty($group->color) || 
            $group->color === '#ffffff' || 
            $group->color === 'white' || 
            $group->color === '#000000' || 
            $group->color === 'black' ||
            !preg_match('/^#[0-9A-Fa-f]{6}$/', $group->color)) {
            
            $needsFix = true;
            
            // Try to match with default colors
            $groupNameLower = strtolower($group->name);
            
            foreach ($defaultColors as $keyword => $color) {
                if (strpos($groupNameLower, $keyword) !== false) {
                    $newColor = $color;
                    break;
                }
            }
            
            // If no match found, use default gray
            if (!$newColor) {
                $newColor = '#6c757d';
            }
        }
        
        if ($needsFix) {
            $oldColor = $group->color ?? 'null';
            $group->update(['color' => $newColor]);
            echo "🔧 Fixed '{$group->name}': {$oldColor} → {$newColor}" . PHP_EOL;
            $fixedCount++;
        } else {
            echo "✅ '{$group->name}': {$group->color} (OK)" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Results ===" . PHP_EOL;
    echo "✅ Fixed {$fixedCount} food group colors" . PHP_EOL;
    echo "📊 Total food groups: {$foodGroups->count()}" . PHP_EOL;
    
    // Show final color distribution
    echo PHP_EOL . "📊 Final color distribution:" . PHP_EOL;
    $colorDistribution = FoodGroup::select('color', DB::raw('count(*) as count'))
        ->groupBy('color')
        ->orderBy('count', 'desc')
        ->get();
    
    foreach ($colorDistribution as $dist) {
        $colorName = $dist->color ?? 'null';
        echo "   - {$colorName}: {$dist->count} groups" . PHP_EOL;
    }
    
    // Create some additional common food groups if they don't exist
    echo PHP_EOL . "=== Creating Missing Common Food Groups ===" . PHP_EOL;
    
    $commonGroups = [
        [
            'name' => 'Sweets',
            'name_translations' => [
                'en' => 'Sweets',
                'ar' => 'حلويات',
                'ku' => 'شیرینی'
            ],
            'description' => 'Candies, chocolates, and sweet treats',
            'color' => '#E91E63',
            'sort_order' => 7
        ],
        [
            'name' => 'Beverages',
            'name_translations' => [
                'en' => 'Beverages',
                'ar' => 'مشروبات',
                'ku' => 'خواردنەوە'
            ],
            'description' => 'Drinks and beverages',
            'color' => '#00BCD4',
            'sort_order' => 8
        ],
        [
            'name' => 'Snacks',
            'name_translations' => [
                'en' => 'Snacks',
                'ar' => 'وجبات خفيفة',
                'ku' => 'خواردنی سووک'
            ],
            'description' => 'Light snacks and finger foods',
            'color' => '#FF5722',
            'sort_order' => 9
        ]
    ];
    
    $createdCount = 0;
    foreach ($commonGroups as $groupData) {
        $existing = FoodGroup::where('name', $groupData['name'])->first();
        if (!$existing) {
            FoodGroup::create([
                'name' => $groupData['name'],
                'name_translations' => $groupData['name_translations'],
                'description' => $groupData['description'],
                'description_translations' => [
                    'en' => $groupData['description'],
                    'ar' => $groupData['description'],
                    'ku' => $groupData['description']
                ],
                'color' => $groupData['color'],
                'sort_order' => $groupData['sort_order'],
                'is_active' => true
            ]);
            echo "✅ Created food group: {$groupData['name']} ({$groupData['color']})" . PHP_EOL;
            $createdCount++;
        }
    }
    
    if ($createdCount === 0) {
        echo "ℹ️  All common food groups already exist" . PHP_EOL;
    }
    
    echo PHP_EOL . "🎉 Food group colors fixed! All category badges should now be visible." . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
