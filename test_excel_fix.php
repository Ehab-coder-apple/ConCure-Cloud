<?php

// Test script to verify Excel template generation
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Exports\FoodsTemplateExport;
use App\Imports\FoodsImport;
use Maatwebsite\Excel\Facades\Excel;

echo "=== Testing Excel Template Generation ===" . PHP_EOL;

try {
    // Test 1: Check if FoodsImport methods work
    echo "1. Testing FoodsImport methods..." . PHP_EOL;
    
    $headers = FoodsImport::getExpectedHeaders();
    echo "   Headers count: " . count($headers) . PHP_EOL;
    echo "   First few headers: " . implode(', ', array_slice(array_keys($headers), 0, 5)) . PHP_EOL;
    
    $sampleData = FoodsImport::getSampleData();
    echo "   Sample data rows: " . count($sampleData) . PHP_EOL;
    echo "   First sample item: " . ($sampleData[0]['name'] ?? 'N/A') . PHP_EOL;
    
    // Test 2: Create export instances
    echo PHP_EOL . "2. Testing FoodsTemplateExport creation..." . PHP_EOL;
    
    $exportWithSample = new FoodsTemplateExport(true);
    $exportEmpty = new FoodsTemplateExport(false);
    
    echo "   Export with sample data created successfully" . PHP_EOL;
    echo "   Export empty template created successfully" . PHP_EOL;
    
    // Test 3: Test export methods
    echo PHP_EOL . "3. Testing export methods..." . PHP_EOL;
    
    $headings = $exportWithSample->headings();
    echo "   Headings count: " . count($headings) . PHP_EOL;
    
    $dataWithSample = $exportWithSample->array();
    echo "   Sample data rows: " . count($dataWithSample) . PHP_EOL;
    
    $dataEmpty = $exportEmpty->array();
    echo "   Empty template rows: " . count($dataEmpty) . PHP_EOL;
    
    // Test 4: Test column widths and styles
    echo PHP_EOL . "4. Testing styling methods..." . PHP_EOL;
    
    $columnWidths = $exportWithSample->columnWidths();
    echo "   Column widths defined: " . count($columnWidths) . PHP_EOL;
    
    $title = $exportWithSample->title();
    echo "   Template title: " . $title . PHP_EOL;
    
    // Test 5: Try to generate actual Excel files
    echo PHP_EOL . "5. Testing Excel file generation..." . PHP_EOL;
    
    // Test with sample data
    $filename1 = 'test_template_with_sample.xlsx';
    try {
        Excel::store($exportWithSample, $filename1, 'local');
        $filePath1 = storage_path('app/' . $filename1);
        if (file_exists($filePath1)) {
            $fileSize1 = filesize($filePath1);
            echo "   ✅ Sample template generated: {$filename1} ({$fileSize1} bytes)" . PHP_EOL;
        } else {
            echo "   ❌ Sample template file not found" . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "   ❌ Sample template generation failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test empty template
    $filename2 = 'test_template_empty.xlsx';
    try {
        Excel::store($exportEmpty, $filename2, 'local');
        $filePath2 = storage_path('app/' . $filename2);
        if (file_exists($filePath2)) {
            $fileSize2 = filesize($filePath2);
            echo "   ✅ Empty template generated: {$filename2} ({$fileSize2} bytes)" . PHP_EOL;
        } else {
            echo "   ❌ Empty template file not found" . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "   ❌ Empty template generation failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test 6: Validate file contents
    echo PHP_EOL . "6. Testing file validation..." . PHP_EOL;
    
    if (isset($filePath1) && file_exists($filePath1)) {
        // Try to read the Excel file back
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($filePath1);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            
            echo "   ✅ Excel file readable: {$highestRow} rows, {$highestColumn} columns" . PHP_EOL;
            
            // Check first few cells
            $cellA1 = $worksheet->getCell('A1')->getValue();
            $cellB1 = $worksheet->getCell('B1')->getValue();
            echo "   First header cells: A1='{$cellA1}', B1='{$cellB1}'" . PHP_EOL;
            
        } catch (Exception $e) {
            echo "   ❌ Excel file validation failed: " . $e->getMessage() . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "=== Test completed successfully! ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}
