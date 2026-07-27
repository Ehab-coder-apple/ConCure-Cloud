<?php

require_once 'vendor/autoload.php';

use App\Imports\FoodsImport;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Collection;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Login as a user
    $user = User::where('role', 'doctor')->first();
    if ($user) {
        Auth::login($user);
        echo 'Logged in as: ' . $user->first_name . ' ' . $user->last_name . PHP_EOL;
        
        // Create test data
        $testData = new Collection([
            [
                'name' => 'Test Apple',
                'name_en' => 'Apple',
                'name_ar' => 'تفاح',
                'name_ku_bahdini' => 'سێو',
                'name_ku_sorani' => 'سێو',
                'food_group' => 'Fruits',
                'calories' => 52,
                'protein' => 0.3,
                'carbohydrates' => 14,
                'fat' => 0.2,
                'fiber' => 2.4,
                'sugar' => 10,
                'sodium' => 1,
                'serving_size' => '100g',
                'description' => 'Fresh apple',
            ]
        ]);
        
        // Test the import
        $import = new FoodsImport();
        $import->collection($testData);
        
        echo 'Import test completed!' . PHP_EOL;
        echo 'Imported: ' . $import->getImportedCount() . PHP_EOL;
        echo 'Skipped: ' . $import->getSkippedCount() . PHP_EOL;
        echo 'Errors: ' . count($import->getErrors()) . PHP_EOL;
        
        if (!empty($import->getErrors())) {
            echo 'Error details:' . PHP_EOL;
            foreach ($import->getErrors() as $error) {
                echo '- ' . $error . PHP_EOL;
            }
        }
    } else {
        echo 'No doctor user found' . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}
