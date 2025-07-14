<?php

namespace App\Services;

use ArPHP\I18N\Arabic;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfKurdishFontService
{
    private $arabic;
    private $fontDir;

    public function __construct()
    {
        $this->arabic = new Arabic();
        $this->fontDir = storage_path('fonts');
        $this->ensureFontsExist();
    }

    /**
     * Ensure required fonts exist
     */
    private function ensureFontsExist()
    {
        if (!is_dir($this->fontDir)) {
            mkdir($this->fontDir, 0755, true);
        }

        // Copy Amiri font if it doesn't exist
        $amiriSource = base_path('vendor/khaled.alshamaa/ar-php/examples/fonts/Amiri-Regular.ttf');
        $amiriDest = $this->fontDir . '/Amiri-Regular.ttf';
        
        if (file_exists($amiriSource) && !file_exists($amiriDest)) {
            copy($amiriSource, $amiriDest);
        }
    }

    /**
     * Create a properly configured DomPDF instance
     */
    public function createPdfInstance()
    {
        $options = new Options();
        $options->set('fontDir', $this->fontDir);
        $options->set('fontCache', $this->fontDir);
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isFontSubsettingEnabled', false); // Disable subsetting for better Arabic support
        $options->set('defaultFont', 'dejavu sans');
        $options->set('defaultMediaType', 'print');
        $options->set('defaultPaperSize', 'A4');
        $options->set('defaultPaperOrientation', 'portrait');

        return new Dompdf($options);
    }

    /**
     * Process Kurdish/Arabic text for PDF rendering
     */
    public function processKurdishText($text)
    {
        if (!$this->isRTLText($text)) {
            return $text;
        }

        try {
            // Use Arabic processor for proper text shaping
            $processedText = $this->arabic->utf8Glyphs($text);
            return $processedText ?: $text;
        } catch (\Exception $e) {
            return $text;
        }
    }

    /**
     * Check if text contains RTL characters
     */
    private function isRTLText($text)
    {
        return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    }

    /**
     * Generate PDF with proper Kurdish font support
     */
    public function generatePdf($html)
    {
        $dompdf = $this->createPdfInstance();
        
        // Load HTML with proper encoding
        $dompdf->loadHtml($html, 'UTF-8');
        
        // Render PDF
        $dompdf->render();
        
        return $dompdf;
    }

    /**
     * Get CSS for Kurdish text styling
     */
    public function getKurdishCss()
    {
        return '
        .kurdish-text {
            font-family: "Amiri-Regular", "dejavu sans";
            direction: rtl;
            text-align: right;
            unicode-bidi: bidi-override;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .arabic-text {
            font-family: "Amiri-Regular", "dejavu sans";
            direction: rtl;
            text-align: right;
            unicode-bidi: bidi-override;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .rtl {
            font-family: "Amiri-Regular", "dejavu sans" !important;
            direction: rtl !important;
            text-align: right !important;
            unicode-bidi: bidi-override !important;
            font-size: 14px !important;
            line-height: 1.8 !important;
        }
        ';
    }
}
