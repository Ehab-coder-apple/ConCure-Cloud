<?php

require_once 'vendor/autoload.php';

use App\Exports\FoodsTemplateExport;
use App\Imports\FoodsImport;
use Maatwebsite\Excel\Facades\Excel;

// Test the template export functionality
echo "Testing Food Template Export...\n";

try {
    // Test 1: Check if FoodsImport class methods work
    echo "1. Testing FoodsImport::getExpectedHeaders()...\n";
    $headers = FoodsImport::getExpectedHeaders();
    echo "   Headers count: " . count($headers) . "\n";
    echo "   First few headers: " . implode(', ', array_slice(array_keys($headers), 0, 5)) . "\n";
    
    // Test 2: Check if FoodsImport sample data works
    echo "2. Testing FoodsImport::getSampleData()...\n";
    $sampleData = FoodsImport::getSampleData();
    echo "   Sample data rows: " . count($sampleData) . "\n";
    if (count($sampleData) > 0) {
        echo "   First row keys: " . implode(', ', array_slice(array_keys($sampleData[0]), 0, 5)) . "\n";
    }
    
    // Test 3: Test FoodsTemplateExport with sample data
    echo "3. Testing FoodsTemplateExport with sample data...\n";
    $exportWithSample = new FoodsTemplateExport(true);
    $arrayData = $exportWithSample->array();
    echo "   Export array rows: " . count($arrayData) . "\n";
    
    // Test 4: Test FoodsTemplateExport without sample data (empty template)
    echo "4. Testing FoodsTemplateExport without sample data...\n";
    $exportEmpty = new FoodsTemplateExport(false);
    $emptyArrayData = $exportEmpty->array();
    echo "   Empty template rows: " . count($emptyArrayData) . "\n";
    
    // Test 5: Test headings
    echo "5. Testing headings...\n";
    $headings = $exportEmpty->headings();
    echo "   Headings count: " . count($headings) . "\n";
    echo "   Headings: " . implode(', ', array_slice($headings, 0, 5)) . "...\n";
    
    // Test 6: Test column widths
    echo "6. Testing column widths...\n";
    $widths = $exportEmpty->columnWidths();
    echo "   Column widths count: " . count($widths) . "\n";
    
    echo "\n✅ All tests passed! Template export should work.\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
