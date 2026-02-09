<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Dompdf\Dompdf;
use Dompdf\Options;

class GenerateFontMetrics extends Command
{
    protected $signature = 'fonts:generate-metrics';
    protected $description = 'Generate font metrics for Kurdish fonts';

    public function handle()
    {
        $this->info('Generating font metrics for Kurdish fonts...');
        
        $fontDir = storage_path('fonts');
        
        // Ensure font directory exists
        if (!is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }
        
        try {
            // Configure DomPDF
            $options = new Options();
            $options->set('fontDir', $fontDir);
            $options->set('fontCache', $fontDir);
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            
            $dompdf = new Dompdf($options);
            
            // Test HTML with Kurdish text to force font loading
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    .amiri { font-family: "amiri-regular", "dejavu sans"; }
                    .navshke { font-family: "navshke-regular", "dejavu sans"; }
                    .noto { font-family: "notosansarabic-regular", "dejavu sans"; }
                </style>
            </head>
            <body>
                <div class="amiri">کوردی - ماسی سەلمۆن</div>
                <div class="navshke">برنجی قاوەیی</div>
                <div class="noto">پەتاتەی شیرین</div>
            </body>
            </html>';
            
            $dompdf->loadHtml($html);
            $dompdf->render();
            
            $this->info('Font metrics generated successfully!');
            
            // List generated font files
            $fontFiles = glob($fontDir . '/*');
            $this->info('Font files in storage/fonts:');
            foreach ($fontFiles as $file) {
                $this->line('  - ' . basename($file));
            }
            
        } catch (\Exception $e) {
            $this->error('Error generating font metrics: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
