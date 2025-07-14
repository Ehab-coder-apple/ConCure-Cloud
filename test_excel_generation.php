<?php

// Simple test to generate Excel file
echo "Testing Excel file generation...\n";

// Create a simple CSV file first to test
$csvData = [
    ['name', 'name_en', 'name_ar', 'name_ku_bahdini', 'name_ku_sorani', 'calories'],
    ['Apple', 'Apple', 'تفاح', 'سێو', 'سێو', '52'],
    ['Banana', 'Banana', 'موز', 'مۆز', 'مۆز', '89'],
    ['Rice', 'Rice', 'أرز', 'برنج', 'برنج', '130']
];

$filename = 'test_foods_template.csv';

// Create CSV file
$file = fopen($filename, 'w');
if ($file) {
    foreach ($csvData as $row) {
        fputcsv($file, $row);
    }
    fclose($file);
    
    echo "✅ CSV file created: $filename\n";
    echo "File size: " . filesize($filename) . " bytes\n";
    
    // Check if file is readable
    if (is_readable($filename)) {
        echo "✅ File is readable\n";
        
        // Show first few lines
        echo "First few lines:\n";
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        foreach (array_slice($lines, 0, 3) as $i => $line) {
            echo "  Line " . ($i + 1) . ": $line\n";
        }
    } else {
        echo "❌ File is not readable\n";
    }
    
} else {
    echo "❌ Could not create CSV file\n";
}

// Test if we can create the file in a different location
$tempFile = sys_get_temp_dir() . '/foods_template_test.csv';
$file2 = fopen($tempFile, 'w');
if ($file2) {
    fputcsv($file2, ['name', 'calories', 'protein']);
    fputcsv($file2, ['Test Food', '100', '5']);
    fclose($file2);
    
    echo "✅ Temp file created: $tempFile\n";
    echo "Temp file size: " . filesize($tempFile) . " bytes\n";
} else {
    echo "❌ Could not create temp file\n";
}

echo "\nTest completed.\n";
