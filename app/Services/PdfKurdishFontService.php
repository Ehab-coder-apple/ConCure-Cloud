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

        // Copy Amiri font if it doesn't exist (lowercase name for DomPDF)
        $amiriSource = base_path('vendor/khaled.alshamaa/ar-php/examples/fonts/Amiri-Regular.ttf');
        $amiriDest = $this->fontDir . '/amiri-regular.ttf';

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
        if (empty($text)) {
            return $text;
        }

        // Clean the text first
        $text = trim($text);

        if (!$this->isRTLText($text)) {
            return $text;
        }

        try {
            // Extract numbers first to preserve their order
            $numbers = [];
            $placeholder_index = 0;

            // Replace numbers with unique placeholders
            $textWithPlaceholders = preg_replace_callback(
                '/[\x{0660}-\x{0669}0-9]+/u',
                function($matches) use (&$numbers, &$placeholder_index) {
                    $placeholder = "___NUM{$placeholder_index}___";
                    $numbers[$placeholder_index] = $matches[0];
                    $placeholder_index++;
                    return $placeholder;
                },
                $text
            );

            // Use utf8Glyphs to convert to presentation forms (connected letters) AND reverse
            // Parameters: text, max_chars, hindo (false = don't convert numerals), forcertl (true = reverse for RTL)
            // mPDF v8+ doesn't respect dir="rtl" for Arabic presentation forms
            // So we need ArPHP to reverse the string for us
            $processedText = $this->arabic->utf8Glyphs($textWithPlaceholders, 1000, false, true);

            // If processing failed or returned empty, return original text
            if (empty($processedText)) {
                \Log::warning('ArPHP returned empty result', ['original' => $text]);
                return $text;
            }

            // Restore numbers by replacing placeholders
            // The placeholders will be reversed, so we need to find them in the processed text
            for ($i = 0; $i < $placeholder_index; $i++) {
                $placeholder = "___NUM{$i}___";
                $reversedPlaceholder = strrev($placeholder);

                // Try both forward and reversed placeholder
                if (strpos($processedText, $placeholder) !== false) {
                    $processedText = str_replace($placeholder, $numbers[$i], $processedText);
                } elseif (strpos($processedText, $reversedPlaceholder) !== false) {
                    $processedText = str_replace($reversedPlaceholder, $numbers[$i], $processedText);
                }
            }

            return $processedText;
        } catch (\Exception $e) {
            \Log::error('Arabic text processing error: ' . $e->getMessage(), [
                'text' => $text,
                'trace' => $e->getTraceAsString()
            ]);
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
