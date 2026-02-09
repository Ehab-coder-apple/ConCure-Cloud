<?php

require __DIR__.'/vendor/autoload.php';

echo "=== mPDF OTL Configuration Test ===\n\n";

$tempDir = __DIR__ . '/storage/mpdf/temp';
if (!is_dir($tempDir)) {
    @mkdir($tempDir, 0755, true);
}

$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];
$fontDirs[] = __DIR__ . '/storage/fonts';

$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

// Test with DejaVu Sans (already has OTL configured in mPDF)
echo "Testing with DejaVu Sans (built-in mPDF font with OTL support)\n";
echo "Font configuration for dejavusans:\n";
if (isset($fontData['dejavusans'])) {
    print_r($fontData['dejavusans']);
} else {
    echo "DejaVu Sans not found in fontdata\n";
}
echo "\n";

echo "\n";

// Create mPDF instance
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'tempDir' => $tempDir,
        'fontDir' => $fontDirs,
        'fontdata' => $fontData,
        'default_font' => 'dejavusans',
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
    ]);
    
    echo "✅ mPDF instance created successfully\n\n";
    
    // Test Arabic text
    $arabicText = 'حبة واحدة';
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: dejavusans; font-size: 16pt; }
        .rtl { direction: rtl; }
    </style>
</head>
<body>
    <h3>Test 1: With dir="rtl" and lang="ar"</h3>
    <div dir="rtl" lang="ar">' . $arabicText . '</div>

    <h3>Test 2: With class rtl</h3>
    <div class="rtl" lang="ar">' . $arabicText . '</div>

    <h3>Test 3: Plain (no dir)</h3>
    <div lang="ar">' . $arabicText . '</div>

    <h3>Test 4: Frequency example</h3>
    <div dir="rtl" lang="ar">ثلاثة مرة</div>

    <h3>Test 5: Duration example</h3>
    <div dir="rtl" lang="ar">يومية١٠</div>
</body>
</html>';
    
    $mpdf->WriteHTML($html);
    $mpdf->Output(__DIR__ . '/otl-test.pdf', 'F');
    
    echo "✅ PDF generated: otl-test.pdf\n";
    echo "\nPlease open otl-test.pdf and check if Arabic letters are connected.\n";
    echo "If letters are still separated, OTL is not working properly.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n=== Font Cache Files ===\n";
$cacheDir = __DIR__ . '/storage/mpdf/temp/mpdf/ttfontdata';
if (is_dir($cacheDir)) {
    $files = scandir($cacheDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $cacheDir . '/' . $file;
            echo "$file - " . filesize($filePath) . " bytes - " . date('Y-m-d H:i:s', filemtime($filePath)) . "\n";
        }
    }
} else {
    echo "Cache directory does not exist\n";
}

