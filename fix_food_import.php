<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FoodGroup;
use Illuminate\Support\Facades\DB;

echo "=== Fixing Food Import Issues ===" . PHP_EOL;

try {
    // 1. Check current food groups
    echo "1. Checking existing food groups..." . PHP_EOL;
    $foodGroups = FoodGroup::all();
    echo "Found " . $foodGroups->count() . " food groups:" . PHP_EOL;
    foreach ($foodGroups as $group) {
        echo "  - ID: {$group->id}, Name: {$group->name}" . PHP_EOL;
    }
    
    // 2. Create default food groups if none exist
    if ($foodGroups->count() == 0) {
        echo "2. Creating default food groups..." . PHP_EOL;
        
        $defaultGroups = [
            [
                'name' => 'General',
                'name_translations' => ['en' => 'General', 'ar' => 'عام', 'ku' => 'گشتی'],
                'description' => 'General food items',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Fruits',
                'name_translations' => ['en' => 'Fruits', 'ar' => 'فواكه', 'ku' => 'میوە'],
                'description' => 'Fresh and dried fruits',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Vegetables',
                'name_translations' => ['en' => 'Vegetables', 'ar' => 'خضروات', 'ku' => 'سەوزە'],
                'description' => 'Fresh and cooked vegetables',
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Proteins',
                'name_translations' => ['en' => 'Proteins', 'ar' => 'بروتينات', 'ku' => 'پرۆتین'],
                'description' => 'Meat, fish, eggs, and legumes',
                'is_active' => true,
                'sort_order' => 4
            ]
        ];
        
        foreach ($defaultGroups as $groupData) {
            $group = FoodGroup::create($groupData);
            echo "  Created: {$group->name} (ID: {$group->id})" . PHP_EOL;
        }
    }
    
    // 3. Check database constraints
    echo "3. Checking database constraints..." . PHP_EOL;
    
    // Check foods table structure
    $columns = DB::select('PRAGMA table_info(foods)');
    echo "Foods table columns:" . PHP_EOL;
    foreach ($columns as $column) {
        $nullable = $column->notnull == 0 ? 'NULL' : 'NOT NULL';
        echo "  - {$column->name} ({$column->type}) {$nullable}" . PHP_EOL;
    }
    
    // Check unique constraints
    $indexes = DB::select('PRAGMA index_list(foods)');
    echo "Foods table indexes:" . PHP_EOL;
    foreach ($indexes as $index) {
        if ($index->unique) {
            $indexInfo = DB::select("PRAGMA index_info({$index->name})");
            $columns = array_map(function($col) { return $col->name; }, $indexInfo);
            echo "  - UNIQUE: " . implode(', ', $columns) . PHP_EOL;
        }
    }
    
    echo "4. Database analysis complete!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
