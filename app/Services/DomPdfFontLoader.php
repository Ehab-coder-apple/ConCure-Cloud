<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\FontMetrics;

class DomPdfFontLoader
{
    private $fontDir;
    private $fontCache;

    public function __construct()
    {
        $this->fontDir = storage_path('fonts');
        $this->fontCache = storage_path('fonts');
        $this->ensureFontDirectories();
    }

    private function ensureFontDirectories()
    {
        if (!is_dir($this->fontDir)) {
            mkdir($this->fontDir, 0755, true);
        }
    }

    /**
     * Load and register fonts with DomPDF
     */
    public function loadFonts()
    {
        // Copy Amiri font if available
        $this->copyAmiriFont();
        
        // Generate font metrics
        $this->generateFontMetrics();
    }

    private function copyAmiriFont()
    {
        $amiriSource = base_path('vendor/khaled.alshamaa/ar-php/examples/fonts/Amiri-Regular.ttf');
        $amiriDest = $this->fontDir . '/amiri-regular.ttf';
        
        if (file_exists($amiriSource) && !file_exists($amiriDest)) {
            copy($amiriSource, $amiriDest);
        }
    }

    private function generateFontMetrics()
    {
        try {
            $options = new Options();
            $options->set('fontDir', $this->fontDir);
            $options->set('fontCache', $this->fontCache);
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            
            $dompdf = new Dompdf($options);
            
            // Force font loading by rendering a test document
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    .test { font-family: "amiri-regular", "dejavu sans"; }
                </style>
            </head>
            <body>
                <div class="test">Test</div>
            </body>
            </html>';
            
            $dompdf->loadHtml($html);
            $dompdf->render();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a properly configured DomPDF instance
     */
    public function createConfiguredDomPdf()
    {
        $options = new Options();
        $options->set('fontDir', $this->fontDir);
        $options->set('fontCache', $this->fontCache);
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isFontSubsettingEnabled', false);
        $options->set('defaultFont', 'dejavu sans');
        $options->set('defaultMediaType', 'print');
        $options->set('defaultPaperSize', 'A4');
        $options->set('defaultPaperOrientation', 'portrait');
        
        // Additional options for better Arabic support
        $options->set('isJavascriptEnabled', false);
        $options->set('debugKeepTemp', false);
        $options->set('debugCss', false);
        $options->set('debugLayout', false);
        $options->set('debugLayoutLines', false);
        $options->set('debugLayoutBlocks', false);
        $options->set('debugLayoutInline', false);
        $options->set('debugLayoutPaddingBox', false);

        return new Dompdf($options);
    }

    /**
     * Get available fonts
     */
    public function getAvailableFonts()
    {
        $fonts = [];
        $fontFiles = glob($this->fontDir . '/*.ttf');
        
        foreach ($fontFiles as $fontFile) {
            $fonts[] = basename($fontFile, '.ttf');
        }
        
        return $fonts;
    }
}
