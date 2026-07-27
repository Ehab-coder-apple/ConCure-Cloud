<?php

// Test script to verify Excel template generation is working
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Exports\FoodsTemplateExport;
use App\Imports\FoodsImport;

echo "=== Testing Excel Template Activation ===" . PHP_EOL;

try {
    // Test 1: Verify FoodsImport static methods
    echo "1. Testing FoodsImport static methods..." . PHP_EOL;
    
    $headers = FoodsImport::getExpectedHeaders();
    if (empty($headers)) {
        throw new Exception("Headers are empty!");
    }
    echo "   ✅ Headers loaded: " . count($headers) . " columns" . PHP_EOL;
    
    $sampleData = FoodsImport::getSampleData();
    if (empty($sampleData)) {
        throw new Exception("Sample data is empty!");
    }
    echo "   ✅ Sample data loaded: " . count($sampleData) . " rows" . PHP_EOL;
    
    // Test 2: Create export instances
    echo PHP_EOL . "2. Testing FoodsTemplateExport creation..." . PHP_EOL;
    
    $exportWithSample = new FoodsTemplateExport(true);
    $exportEmpty = new FoodsTemplateExport(false);
    
    echo "   ✅ Export instances created successfully" . PHP_EOL;
    
    // Test 3: Test required methods
    echo PHP_EOL . "3. Testing required interface methods..." . PHP_EOL;
    
    // Test headings method
    $headings = $exportWithSample->headings();
    if (empty($headings)) {
        throw new Exception("Headings method returned empty array!");
    }
    echo "   ✅ headings() method: " . count($headings) . " headers" . PHP_EOL;
    
    // Test array method with sample data
    $arrayWithSample = $exportWithSample->array();
    if (empty($arrayWithSample)) {
        throw new Exception("Array method with sample data returned empty!");
    }
    echo "   ✅ array() with sample: " . count($arrayWithSample) . " rows" . PHP_EOL;
    
    // Test array method with empty template
    $arrayEmpty = $exportEmpty->array();
    if (empty($arrayEmpty)) {
        throw new Exception("Array method for empty template returned empty!");
    }
    echo "   ✅ array() empty template: " . count($arrayEmpty) . " rows" . PHP_EOL;
    
    // Test styles method
    $mockWorksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet();
    $styles = $exportWithSample->styles($mockWorksheet);
    echo "   ✅ styles() method executed successfully" . PHP_EOL;
    
    // Test 4: Validate data structure
    echo PHP_EOL . "4. Validating data structure..." . PHP_EOL;
    
    $firstSampleRow = $arrayWithSample[0];
    $expectedHeaders = array_keys($headers);
    
    foreach ($expectedHeaders as $header) {
        if (!array_key_exists($header, $firstSampleRow)) {
            throw new Exception("Missing header '{$header}' in sample data!");
        }
    }
    echo "   ✅ All expected headers present in sample data" . PHP_EOL;
    
    $firstEmptyRow = $arrayEmpty[0];
    foreach ($expectedHeaders as $header) {
        if (!array_key_exists($header, $firstEmptyRow)) {
            throw new Exception("Missing header '{$header}' in empty template!");
        }
    }
    echo "   ✅ All expected headers present in empty template" . PHP_EOL;
    
    // Test 5: Check multilingual support
    echo PHP_EOL . "5. Testing multilingual support..." . PHP_EOL;
    
    $multilingualHeaders = ['name_en', 'name_ar', 'name_ku_bahdini', 'name_ku_sorani'];
    foreach ($multilingualHeaders as $mlHeader) {
        if (!in_array($mlHeader, $expectedHeaders)) {
            throw new Exception("Missing multilingual header: {$mlHeader}");
        }
    }
    echo "   ✅ All multilingual headers present" . PHP_EOL;
    
    // Check if sample data has multilingual content
    $firstSample = $arrayWithSample[0];
    if (!empty($firstSample['name_ar']) && !empty($firstSample['name_ku_bahdini'])) {
        echo "   ✅ Sample data contains multilingual content" . PHP_EOL;
    } else {
        echo "   ⚠️  Sample data missing some multilingual content" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== ✅ Excel Template Activation Test PASSED! ===" . PHP_EOL;
    echo PHP_EOL . "Excel template generation is ready to use:" . PHP_EOL;
    echo "- All required methods implemented" . PHP_EOL;
    echo "- Data structure is valid" . PHP_EOL;
    echo "- Multilingual support is active" . PHP_EOL;
    echo "- Both sample and empty templates work" . PHP_EOL;
    echo PHP_EOL . "You can now download Excel templates from /foods/import" . PHP_EOL;
    
} catch (Exception $e) {
    echo PHP_EOL . "❌ Test FAILED: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}
