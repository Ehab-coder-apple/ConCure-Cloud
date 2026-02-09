<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Dompdf\Dompdf;
use Dompdf\Options;

class LoadFonts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fonts:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load and register fonts for PDF generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Loading fonts for PDF generation...');

        // Ensure font directories exist
        $fontDir = storage_path('fonts');
        if (!is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
            $this->info("Created font directory: {$fontDir}");
        }

        // Copy Amiri font if it doesn't exist
        $amiriSource = base_path('vendor/khaled.alshamaa/ar-php/examples/fonts/Amiri-Regular.ttf');
        $amiriDest = $fontDir . '/Amiri-Regular.ttf';

        if (file_exists($amiriSource) && !file_exists($amiriDest)) {
            copy($amiriSource, $amiriDest);
            $this->info('Copied Amiri font to storage/fonts/');
        }

        // Test font loading
        try {
            $options = new Options();
            $options->set('fontDir', $fontDir);
            $options->set('fontCache', $fontDir);
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);

            // Test HTML with Kurdish text
            $html = '
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    .kurdish {
                        font-family: "Amiri", "dejavu sans";
                        direction: rtl;
                        text-align: right;
                        font-size: 16px;
                    }
                </style>
            </head>
            <body>
                <div class="kurdish">کوردی - ماسی سەلمۆن - برنجی قاوەیی</div>
            </body>
            </html>';

            $dompdf->loadHtml($html);
            $dompdf->render();

            $this->info('Font loading test completed successfully!');
            $this->info('Available fonts in storage/fonts/:');

            $fonts = glob($fontDir . '/*.ttf');
            foreach ($fonts as $font) {
                $this->line('  - ' . basename($font));
            }

        } catch (\Exception $e) {
            $this->error('Font loading failed: ' . $e->getMessage());
        }

        return 0;
    }
}
