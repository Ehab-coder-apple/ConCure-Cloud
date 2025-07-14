<?php

namespace App\Services;

use ArPHP\I18N\Arabic;

class KurdishFontService
{
    private $arabic;

    public function __construct()
    {
        $this->arabic = new Arabic();
    }

    /**
     * Process Kurdish/Arabic text for proper PDF rendering
     */
    public function processText($text)
    {
        if (!$this->isRTLText($text)) {
            return $text;
        }

        try {
            // Use Arabic processor to shape the text properly
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
     * Check if text contains Kurdish-specific characters
     */
    public function isKurdishText($text)
    {
        return preg_match('/[ڕڵێۆۊڤگکچژ]/u', $text);
    }

    /**
     * Get appropriate CSS class for text direction
     */
    public function getTextDirectionClass($text)
    {
        if (!$this->isRTLText($text)) {
            return 'ltr';
        }

        return $this->isKurdishText($text) ? 'rtl kurdish-text' : 'rtl arabic-text';
    }

    /**
     * Configure DomPDF options for Kurdish font support
     */
    public function configurePdfOptions($pdf)
    {
        $pdf->getDomPDF()->getOptions()->set('isRemoteEnabled', true);
        $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->getOptions()->set('isPhpEnabled', true);
        $pdf->getDomPDF()->getOptions()->set('fontDir', storage_path('fonts'));
        $pdf->getDomPDF()->getOptions()->set('fontCache', storage_path('fonts'));
        $pdf->getDomPDF()->getOptions()->set('isFontSubsettingEnabled', true);
        $pdf->getDomPDF()->getOptions()->set('defaultFont', 'amiri-regular');
        
        return $pdf;
    }
}
