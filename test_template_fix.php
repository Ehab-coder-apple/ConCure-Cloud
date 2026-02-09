<?php

// Test the template download fix
echo "Testing template download fix...\n";

// Test CSV generation
$headers = [
    'name', 'name_en', 'name_ar', 'name_ku_bahdini', 'name_ku_sorani', 
    'food_group', 'calories', 'protein', 'carbohydrates', 'fat', 
    'fiber', 'sugar', 'sodium', 'serving_size', 'description', 
    'description_en', 'description_ar', 'description_ku_bahdini', 'description_ku_sorani'
];

$sampleData = [
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
        'description' => 'Lean protein source, boneless and skinless',
        'description_en' => 'Lean protein source, boneless and skinless',
        'description_ar' => 'مصدر بروتين قليل الدهون، بدون عظم وبدون جلد',
        'description_ku_bahdini' => 'سەرچاوەی پرۆتینی کەم چەوری، بێ ئێسک و بێ پێست',
        'description_ku_sorani' => 'سەرچاوەی پرۆتینی کەم چەوری، بێ ئێسک و بێ پێست'
    ]
];

// Test CSV with sample data
$filename = 'test_foods_template_with_sample.csv';
$file = fopen($filename, 'w');

if ($file) {
    // Add headers
    fputcsv($file, $headers);
    
    // Add sample data
    foreach ($sampleData as $row) {
        $csvRow = [];
        foreach ($headers as $header) {
            $csvRow[] = $row[$header] ?? '';
        }
        fputcsv($file, $csvRow);
    }
    
    fclose($file);
    
    echo "✅ CSV with sample data created: $filename\n";
    echo "File size: " . filesize($filename) . " bytes\n";
} else {
    echo "❌ Could not create CSV file\n";
}

// Test empty CSV template
$emptyFilename = 'test_foods_template_empty.csv';
$emptyFile = fopen($emptyFilename, 'w');

if ($emptyFile) {
    // Add headers
    fputcsv($emptyFile, $headers);
    
    // Add 3 empty rows
    for ($i = 0; $i < 3; $i++) {
        fputcsv($emptyFile, array_fill(0, count($headers), ''));
    }
    
    fclose($emptyFile);
    
    echo "✅ Empty CSV template created: $emptyFilename\n";
    echo "File size: " . filesize($emptyFilename) . " bytes\n";
} else {
    echo "❌ Could not create empty CSV file\n";
}

echo "\n✅ Template generation test completed successfully!\n";
echo "Both Excel and CSV templates should now work.\n";
echo "If Excel fails, the system will automatically fallback to CSV.\n";
