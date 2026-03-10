<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Support\Facades\Storage;

class CustomTemplateService
{
    /**
     * Supported report types and their setting key prefixes.
     */
    public const REPORT_TYPES = [
        'prescription'   => ['label' => 'Prescription', 'prefix' => 'rx', 'icon' => 'fa-file-prescription'],
        'blank_report'   => ['label' => 'Medical Report', 'prefix' => 'report', 'icon' => 'fa-file-medical'],
        'radiology'      => ['label' => 'Radiology Request', 'prefix' => 'radiology', 'icon' => 'fa-x-ray'],
        'lab_request'    => ['label' => 'Lab Request', 'prefix' => 'lab', 'icon' => 'fa-flask'],
        'diet_plan'      => ['label' => 'Diet / Nutrition Plan', 'prefix' => 'diet', 'icon' => 'fa-utensils'],
        'dental'         => ['label' => 'Dental Treatment', 'prefix' => 'dental', 'icon' => 'fa-tooth'],
        'invoice'        => ['label' => 'Invoice', 'prefix' => 'invoice', 'icon' => 'fa-file-invoice-dollar'],
    ];

    /**
     * Get all template settings for a given report type from a clinic.
     */
    public static function getSettings(Clinic $clinic, string $reportType): array
    {
        $prefix = self::REPORT_TYPES[$reportType]['prefix'] ?? $reportType;
        return [
            'enabled'      => filter_var($clinic->getSetting("{$prefix}_template_enabled", false), FILTER_VALIDATE_BOOLEAN),
            'path'         => $clinic->getSetting("{$prefix}_template_path", ''),
            'content_x'    => (int) $clinic->getSetting("{$prefix}_content_x", 40),
            'content_y'    => (int) $clinic->getSetting("{$prefix}_content_y", 200),
            'font_size'    => (int) $clinic->getSetting("{$prefix}_font_size", 11),
            'line_spacing' => (int) $clinic->getSetting("{$prefix}_line_spacing", 22),
        ];
    }

    /**
     * Download template to a local temp file and return the path + metadata.
     * Returns null on failure.
     */
    public static function prepareTemplate(Clinic $clinic, string $reportType, bool $forceCustom = false): ?array
    {
        $settings = self::getSettings($clinic, $reportType);

        $useCustom = $forceCustom || $settings['enabled'];
        $templatePath = $settings['path'];

        if (!$useCustom || !$templatePath) {
            return null;
        }

        try {
            $ext = strtolower(pathinfo($templatePath, PATHINFO_EXTENSION));
            $isPdf = ($ext === 'pdf');
            $tempFile = storage_path("app/temp_tpl_{$reportType}_{$clinic->id}.{$ext}");
            $contents = Storage::disk(StorageQuotaService::SPACES_DISK)->get($templatePath);

            if (!$contents) {
                return null;
            }

            file_put_contents($tempFile, $contents);

            return [
                'localPath'      => $tempFile,
                'isPdf'          => $isPdf,
                'imagePath'      => $isPdf ? null : $tempFile,
                'settings'       => $settings,
            ];
        } catch (\Exception $e) {
            \Log::error("Custom template download failed for {$reportType}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create a configured mPDF instance, optionally with a custom template applied.
     */
    public static function createMpdf(?array $templateData = null): \Mpdf\Mpdf
    {
        $tempDir = storage_path('mpdf/temp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $fontDirs[] = storage_path('fonts');

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $config = [
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'tempDir'           => $tempDir,
            'fontDir'           => $fontDirs,
            'fontdata'          => $fontData,
            'default_font'      => 'dejavusans',
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
        ];

        if ($templateData) {
            $config['margin_top'] = 0;
            $config['margin_bottom'] = 0;
            $config['margin_left'] = 0;
            $config['margin_right'] = 0;
        }

        $mpdf = new \Mpdf\Mpdf($config);

        if ($templateData && $templateData['isPdf']) {
            $mpdf->SetDocTemplate($templateData['localPath'], true);
        }

        return $mpdf;
    }

    /**
     * Clean up the temp template file.
     */
    public static function cleanup(?array $templateData): void
    {
        if ($templateData && !empty($templateData['localPath']) && file_exists($templateData['localPath'])) {
            @unlink($templateData['localPath']);
        }
    }
}

