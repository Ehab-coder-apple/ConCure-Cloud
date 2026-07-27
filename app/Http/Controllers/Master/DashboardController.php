<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the master dashboard.
     */
    public function index()
    {
        // Get system statistics
        $stats = $this->getSystemStats();

        // Get recent activities
        $recentClinics = $this->getRecentClinics();
        $recentUsers = $this->getRecentUsers();

        // Clinics whose contract is up for renewal next calendar month
        $upcomingRenewals = $this->getUpcomingRenewals();

        // Get growth data for charts
        $growthData = $this->getGrowthData();

        return view('master.dashboard', compact('stats', 'recentClinics', 'recentUsers', 'upcomingRenewals', 'growthData'));
    }

    /**
     * Clinics whose contract_renewal_at falls within next calendar month.
     * Excludes demo clinics. Indexed column keeps this cheap on large datasets.
     */
    private function getUpcomingRenewals()
    {
        if (!Schema::hasColumn('clinics', 'contract_renewal_at')) {
            return collect();
        }

        $start = Carbon::now()->addMonthNoOverflow()->startOfMonth()->toDateString();
        $end   = Carbon::now()->addMonthNoOverflow()->endOfMonth()->toDateString();

        $query = Clinic::query()
            ->select(['id', 'name', 'email', 'city', 'is_active', 'contract_renewal_at'])
            ->whereNotNull('contract_renewal_at')
            ->whereBetween('contract_renewal_at', [$start, $end]);

        if (Schema::hasColumn('clinics', 'is_demo')) {
            $query->where('is_demo', false);
        }

        return $query->orderBy('contract_renewal_at')->limit(50)->get();
    }

    /**
     * Show comprehensive features documentation.
     */
    public function features()
    {
        return view('master.features');
    }

    /**
     * Generate PDF of features documentation.
     *
     * The language is driven by an optional ?lang= query parameter (so the
     * Master UI — which is English-only — can still offer a one-click Arabic
     * download without switching the whole session locale). When ?lang is
     * absent we fall back to the active app locale.
     *
     * Arabic dispatches to the mPDF renderer below — DomPDF can't shape
     * Arabic glyphs or handle bidi. Everything else uses DomPDF.
     */
    public function featuresPdf(\Illuminate\Http\Request $request)
    {
        $lang = (string) $request->query('lang', '');
        $lang = in_array($lang, ['en', 'ar'], true) ? $lang : app()->getLocale();

        if ($lang === 'ar') {
            return $this->featuresPdfArabic();
        }

        // Build a base64 data URI for the footer logo. Using base64 inside a
        // position:fixed HTML <img> bypasses both DomPDF's chroot URL check
        // and the $canvas->image() RGBA tempnam() path, which silently fail
        // on some shared hosts. The same technique already powers the cover
        // logo. Prefer the flat JPEG (smaller, no alpha edge cases); fall
        // back to the source PNG.
        $footerLogoSrc = self::buildFooterLogoDataUri();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('master.features-pdf', [
            'footerLogoSrc' => $footerLogoSrc,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'sans-serif',
            'dpi' => 120,
            'defaultPaperSize' => 'a4',
        ]);

        // Per-page footer text + page numbers. Cover page #1 is skipped.
        // The logo is rendered via a position:fixed <img> in the blade so we
        // don't depend on $canvas->image() at all.
        $hasLogo = (bool) $footerLogoSrc;

        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($hasLogo) {
            if ($pageNumber === 1) {
                return;
            }
            $bold = $fontMetrics->getFont('Helvetica', 'bold');
            $regular = $fontMetrics->getFont('Helvetica', 'normal');
            $size = 9;
            $brand = [0.043, 0.227, 0.549];
            $muted = [0.42, 0.45, 0.50];
            $pageWidth = $canvas->get_width();

            // ---- Footer (bottom of page): brand text on left, page numbers on right ----
            // The logo itself is drawn by the position:fixed <img> in the
            // blade (12mm ≈ 34pt wide). We just leave room for it on the
            // left when present.
            $y = $canvas->get_height() - 30;
            $textX = $hasLogo ? (45 + 34 + 6) : 45;

            $canvas->text($textX, $y, 'CONCURE CLOUD', $bold, $size, $brand);
            $canvas->text(
                $textX + $fontMetrics->getTextWidth('CONCURE CLOUD', $bold, $size) + 6,
                $y,
                '·  Complete Feature List',
                $regular, $size, $muted
            );

            $text = 'Page ' . ($pageNumber - 1) . ' of ' . ($pageCount - 1);
            $width = $fontMetrics->getTextWidth($text, $regular, $size);
            $canvas->text($pageWidth - $width - 45, $y, $text, $regular, $size, $muted);
        });

        return $pdf->download('ConCure-Cloud-Features-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Render the Features List PDF with Arabic content using mPDF.
     *
     * mPDF (already used for prescription PDFs in this codebase) handles
     * Arabic glyph shaping and RTL bidi natively, both of which DomPDF
     * lacks. The Amiri-Regular.ttf in storage/fonts/ is the same font
     * used by the existing Kurdish/Arabic prescription path.
     *
     * The cover and content live in master/features-pdf-ar.blade.php. The
     * per-page footer (logo + brand text + page numbers) is set via
     * SetHTMLFooter(); first-page footer is suppressed by the @page :first
     * rule inside the blade so the cover stays clean.
     */
    private function featuresPdfArabic()
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

        // We deliberately do NOT register Amiri here. The Amiri-Regular.ttf
        // build present in storage/fonts/ uses GPOS Lookup Type 5 Format 3,
        // which mPDF's TTFontFile parser cannot decode (throws FontException
        // at TTFontFile.php:1794). Instead we lean on mPDF's bundled Arabic
        // shaping font 'xbriyaz' (already used implicitly by the working
        // prescription PDF path via autoLangToFont) — it ships with the
        // package, has full OTL support, and avoids the broken TTF entirely.

        // Disable tatweel (kashida) insertion across ALL Arabic-capable
        // fonts. mPDF ships every Arabic font in its bundled fontdata with
        // 'useKashida' => 75 (xbriyaz, lateef, kfgqpcuthmantahanaskh), and
        // because we run with autoLangToFont => true mPDF is free to pick
        // any of them for Arabic runs — overriding only 'xbriyaz' leaves
        // the door open. Sweeping the whole array also future-proofs us
        // against new Arabic fonts being added in mPDF upgrades.
        //
        // Visible symptom when this is on: long horizontal connector
        // strokes (the tatweel ـ glyph) inserted between letter clusters
        // to stretch each justified line, which read as a baseline bar
        // crossing through the words.
        foreach ($fontData as $family => &$cfg) {
            if (is_array($cfg) && array_key_exists('useKashida', $cfg)) {
                $cfg['useKashida'] = 0;
            }
        }
        unset($cfg);

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'tempDir'           => $tempDir,
            'fontDir'           => $fontDirs,
            'fontdata'          => $fontData,
            'default_font'      => 'xbriyaz',
            'default_font_size' => 11,
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
            'directionality'    => 'rtl',
            'margin_left'       => 16,
            'margin_right'      => 16,
            'margin_top'        => 22,
            'margin_bottom'     => 24,
            'margin_header'     => 8,
            'margin_footer'     => 10,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->SetTitle('ConCure Cloud - قائمة الميزات الكاملة');

        $footerLogoSrc = self::buildFooterLogoDataUri();

        // The named footer ("brandFooter") is declared inside the blade via
        // <htmlpagefooter>, and toggled OFF on the cover / ON for body pages
        // via <sethtmlpagefooter> directives — that lets a single WriteHTML
        // call render both pages without us having to split the cover.
        $html = view('master.features-pdf-ar', [
            'footerLogoSrc' => $footerLogoSrc,
        ])->render();

        $mpdf->WriteHTML($html);

        $filename = 'ConCure-Cloud-Features-AR-' . date('Y-m-d') . '.pdf';
        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }


    /**
     * Build a base64 data URI for the per-page footer logo, preferring the
     * pre-flattened JPEG and falling back to the original PNG. Returns null
     * when no logo is configured or the file cannot be read.
     */
    private static function buildFooterLogoDataUri(): ?string
    {
        $candidates = [];

        $flat = \App\Http\Controllers\Master\SettingsController::getMasterBrandingLogoFlatJpegPath();
        if ($flat) {
            $candidates[] = $flat;
        }

        $rel = \App\Http\Controllers\Master\SettingsController::getMasterBrandingLogoForPdfRelPath();
        if ($rel) {
            $candidates[] = public_path($rel);
        }

        foreach ($candidates as $path) {
            if (!is_string($path) || !file_exists($path) || !is_readable($path)) {
                continue;
            }
            $bytes = @file_get_contents($path);
            if ($bytes === false || $bytes === '') {
                continue;
            }
            $info = @getimagesize($path);
            $mime = $info['mime'] ?? null;
            if (!in_array($mime, ['image/png', 'image/jpeg'], true)) {
                continue;
            }
            return 'data:' . $mime . ';base64,' . base64_encode($bytes);
        }

        return null;
    }

    /**
     * Get pending clinic registrations for approval.
     */
    public function getPendingRegistrations()
    {
        $this->authorizeGlobalRoot();

        $pendingClinics = Clinic::where('is_active', false)
            ->with(['users' => function($q) {
                $q->where('role', 'admin');
            }])
            ->latest()
            ->get();

        return response()->json($pendingClinics);
    }

    /**
     * Approve a clinic registration.
     */
    public function approveClinic(Clinic $clinic)
    {
        $this->authorizeGlobalRoot();

        if ($clinic->is_active) {
            return response()->json(['error' => 'Clinic is already active'], 400);
        }

        DB::beginTransaction();
        try {
            // Activate clinic
            $clinic->update([
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // Activate admin users
            $clinic->users()->where('role', 'admin')->update([
                'is_active' => true,
                'activated_at' => now(),
            ]);

            DB::commit();

            // TODO: Send approval email to clinic admin
            \Log::info('Clinic approved', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'approved_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clinic approved successfully',
                'clinic' => $clinic->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to approve clinic: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject a clinic registration.
     */
    public function rejectClinic(Request $request, Clinic $clinic)
    {
        $this->authorizeGlobalRoot();

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($clinic->is_active) {
            return response()->json(['error' => 'Cannot reject an active clinic'], 400);
        }

        DB::beginTransaction();
        try {
            // Update clinic settings with rejection reason
            $settings = json_decode($clinic->settings, true) ?? [];
            $settings['rejection_reason'] = $request->reason;
            $settings['rejected_at'] = now()->toISOString();
            $settings['rejected_by'] = auth()->id();

            $clinic->update([
                'settings' => json_encode($settings),
            ]);

            DB::commit();

            // TODO: Send rejection email to clinic admin
            \Log::info('Clinic rejected', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'reason' => $request->reason,
                'rejected_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clinic registration rejected',
                'clinic' => $clinic->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to reject clinic: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get system-wide statistics.
     */
    private function getSystemStats()
    {
        $clinicQuery = $this->accessibleClinicsQuery();
        $clinicIds = $this->accessibleClinicIds();

        $userQuery = User::where('role', '!=', 'super_admin');
        if (!$this->hasGlobalClinicAccess()) {
            $userQuery->whereIn('clinic_id', $clinicIds);
        }

        $prescriptionQuery = Prescription::query();
        $appointmentQuery = Appointment::query();

        if (!$this->hasGlobalClinicAccess()) {
            $prescriptionQuery->whereIn('clinic_id', $clinicIds);
            $appointmentQuery->whereIn('clinic_id', $clinicIds);
        }

        return [
            'total_clinics' => (clone $clinicQuery)->count(),
            'active_clinics' => (clone $clinicQuery)->where('is_active', true)->count(),
            'pending_clinics' => (clone $clinicQuery)->where('is_active', false)->count(),
            'total_users' => (clone $userQuery)->count(),
            'active_users' => (clone $userQuery)->where('is_active', true)->count(),
            'total_patients' => Patient::count(),
            'total_prescriptions' => $prescriptionQuery->count(),
            'total_appointments' => $appointmentQuery->count(),
            'monthly_new_clinics' => (clone $clinicQuery)->whereMonth('created_at', now()->month)->count(),
            'monthly_new_users' => (clone $userQuery)->whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Get recent clinics.
     */
    private function getRecentClinics()
    {
        $query = Clinic::with(['users' => function($query) {
            $query->where('role', 'admin')->first();
        }]);

        $this->applyAccessibleClinicScope($query);

        return $query->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get recent users.
     */
    private function getRecentUsers()
    {
        $query = User::with('clinic')
            ->where('role', '!=', 'super_admin');

        if (!$this->hasGlobalClinicAccess()) {
            $query->whereIn('clinic_id', $this->accessibleClinicIds());
        }

        return $query
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get growth data for charts.
     */
    private function getGrowthData()
    {
        $months = [];
        $clinicData = [];
        $userData = [];
        $patientData = [];
        $clinicIds = $this->accessibleClinicIds();

        // Get data for last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            $clinicMonthQuery = Clinic::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            $userMonthQuery = User::where('role', '!=', 'super_admin')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            $patientMonthQuery = Patient::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            if (!$this->hasGlobalClinicAccess()) {
                $clinicMonthQuery->whereIn('id', $clinicIds);
                $userMonthQuery->whereIn('clinic_id', $clinicIds);
            }

            $clinicData[] = $clinicMonthQuery->count();
            $userData[] = $userMonthQuery->count();
            $patientData[] = $patientMonthQuery->count();
        }

        return [
            'months' => $months,
            'clinics' => $clinicData,
            'users' => $userData,
            'patients' => $patientData,
        ];
    }

    /**
     * Get clinic distribution by status.
     */
    public function getClinicStatusData()
    {
        $query = Clinic::query();
        $this->applyAccessibleClinicScope($query);

        $data = [
            'active' => (clone $query)->where('is_active', true)->count(),
            'inactive' => (clone $query)->where('is_active', false)->count(),
        ];

        return response()->json($data);
    }

    /**
     * Get user distribution by role.
     */
    public function getUserRoleData()
    {
        $query = User::where('role', '!=', 'super_admin');

        if (!$this->hasGlobalClinicAccess()) {
            $query->whereIn('clinic_id', $this->accessibleClinicIds());
        }

        $data = $query
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return response()->json($data);
    }

    /**
     * Get system health status.
     */
    public function getSystemHealth()
    {
        $this->authorizeGlobalRoot();

        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'cache' => $this->checkCacheHealth(),
        ];

        return response()->json($health);
    }

    /**
     * Check database health.
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection is working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    /**
     * Check storage health.
     */
    private function checkStorageHealth()
    {
        try {
            $diskSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usedPercentage = (($totalSpace - $diskSpace) / $totalSpace) * 100;

            if ($usedPercentage > 90) {
                return ['status' => 'warning', 'message' => 'Storage usage is high (' . round($usedPercentage, 1) . '%)'];
            }

            return ['status' => 'healthy', 'message' => 'Storage usage is normal (' . round($usedPercentage, 1) . '%)'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Unable to check storage'];
        }
    }

    /**
     * Check cache health.
     */
    private function checkCacheHealth()
    {
        try {
            cache()->put('health_check', 'test', 60);
            $value = cache()->get('health_check');

            if ($value === 'test') {
                return ['status' => 'healthy', 'message' => 'Cache is working'];
            }

            return ['status' => 'warning', 'message' => 'Cache test failed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache connection failed'];
        }
    }

    private function accessibleClinicIds(): array
    {
        return auth()->user()?->accessibleClinicIds() ?? [];
    }

    private function hasGlobalClinicAccess(): bool
    {
        return auth()->user()?->hasGlobalClinicAccess() ?? false;
    }

    private function accessibleClinicsQuery()
    {
        $query = Clinic::query();
        $this->applyAccessibleClinicScope($query);

        return $query;
    }

    private function applyAccessibleClinicScope($query)
    {
        if ($this->hasGlobalClinicAccess()) {
            return $query;
        }

        $clinicIds = $this->accessibleClinicIds();

        return $clinicIds === []
            ? $query->whereRaw('1 = 0')
            : $query->whereIn('id', $clinicIds);
    }

    private function authorizeGlobalRoot(): void
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Only the Master Admin can perform this action.');
        }
    }
}
