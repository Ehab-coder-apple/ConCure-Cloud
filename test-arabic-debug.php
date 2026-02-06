<?php

require __DIR__.'/vendor/autoload.php';

use ArPHP\I18N\Arabic;

echo "=== Arabic Text Rendering Debug ===\n\n";

// Test strings
$testStrings = [
    'حبة واحدة',
    'يوميا',
    'قبل الأكل',
];

$arabic = new Arabic();

foreach ($testStrings as $original) {
    echo "Original: $original\n";
    echo "Hex: " . bin2hex($original) . "\n";
    
    // Test with forcertl = false
    $processed_no_rtl = $arabic->utf8Glyphs($original, 1000, false, false);
    echo "ArPHP (forcertl=false): $processed_no_rtl\n";
    echo "Hex: " . bin2hex($processed_no_rtl) . "\n";
    
    // Test with forcertl = true
    $processed_rtl = $arabic->utf8Glyphs($original, 1000, false, true);
    echo "ArPHP (forcertl=true): $processed_rtl\n";
    echo "Hex: " . bin2hex($processed_rtl) . "\n";
    
    echo "\n---\n\n";
}

// Now test mPDF rendering
echo "=== Testing mPDF Rendering ===\n\n";

$tempDir = __DIR__ . '/storage/mpdf/temp';
if (!is_dir($tempDir)) {
    @mkdir($tempDir, 0755, true);
}

$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];
$fontDirs[] = __DIR__ . '/storage/fonts';

$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$fontData['amiri'] = [
    'R' => 'amiri-regular.ttf',
];

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'tempDir' => $tempDir,
    'fontDir' => $fontDirs,
    'fontdata' => $fontData,
    'default_font' => 'amiri',
]);

// Test 1: Original text with dir="rtl"
$html1 = '<html><body style="font-family: amiri; font-size: 16pt;">
<h3>Test 1: Original + dir="rtl"</h3>
<div dir="rtl">حبة واحدة</div>
</body></html>';

$mpdf->WriteHTML($html1);
$mpdf->AddPage();

// Test 2: ArPHP processed (forcertl=false) with dir="rtl"
$processed1 = $arabic->utf8Glyphs('حبة واحدة', 1000, false, false);
$html2 = '<html><body style="font-family: amiri; font-size: 16pt;">
<h3>Test 2: ArPHP (forcertl=false) + dir="rtl"</h3>
<div dir="rtl">' . $processed1 . '</div>
</body></html>';

$mpdf->WriteHTML($html2);
$mpdf->AddPage();

// Test 3: ArPHP processed (forcertl=true) without dir
$processed2 = $arabic->utf8Glyphs('حبة واحدة', 1000, false, true);
$html3 = '<html><body style="font-family: amiri; font-size: 16pt;">
<h3>Test 3: ArPHP (forcertl=true) no dir</h3>
<div>' . $processed2 . '</div>
</body></html>';

$mpdf->WriteHTML($html3);
$mpdf->AddPage();

// Test 4: ArPHP processed (forcertl=true) with dir="ltr"
$html4 = '<html><body style="font-family: amiri; font-size: 16pt;">
<h3>Test 4: ArPHP (forcertl=true) + dir="ltr"</h3>
<div dir="ltr">' . $processed2 . '</div>
</body></html>';

$mpdf->WriteHTML($html4);

$mpdf->Output(__DIR__ . '/arabic-test.pdf', 'F');

echo "PDF generated: arabic-test.pdf\n";
echo "Please open and check which test looks correct.\n";

