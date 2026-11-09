<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Phase 3 coverage: the shared components.storage-status-alert partial
 * (used by both the tenant storage widget and the Master clinic show page)
 * renders the correct one of three explicit states purely from
 * StorageQuotaService::getStorageInfo()'s 'warning'/'critical' flags.
 */
class StorageStatusAlertComponentTest extends TestCase
{
    private function render(float $percentage): string
    {
        $storageInfo = [
            'percentage_used' => $percentage,
            'used_gb' => round($percentage * 10 / 100, 2),
            'limit_gb' => 10,
            'warning' => $percentage >= 90 && $percentage < 100,
            'critical' => $percentage >= 100,
        ];

        return view('components.storage-status-alert', compact('storageInfo'))->render();
    }

    public function test_under_90_percent_renders_default_ok_state_without_any_alert_banner(): void
    {
        $html = $this->render(50.0);

        $this->assertStringContainsString('data-storage-state="ok"', $html);
        $this->assertStringNotContainsString('alert-warning', $html);
        $this->assertStringNotContainsString('alert-danger', $html);
        $this->assertStringContainsString('bg-success', $html);
    }

    public function test_90_to_99_percent_renders_amber_warning_banner(): void
    {
        $html = $this->render(92.5);

        $this->assertStringContainsString('data-storage-state="warning"', $html);
        $this->assertStringContainsString('alert-warning', $html);
        $this->assertStringContainsString('border-warning', $html);
        $this->assertStringContainsString('nearing your storage capacity', $html);
        $this->assertStringNotContainsString('alert-danger', $html);
    }

    public function test_100_percent_or_more_renders_critical_banner_with_required_text(): void
    {
        $html = $this->render(100.0);

        $this->assertStringContainsString('data-storage-state="critical"', $html);
        $this->assertStringContainsString('alert-danger', $html);
        $this->assertStringContainsString('storage-alert-flash', $html);
        $this->assertStringContainsString('⚠️ Storage Limit Exceeded', $html);
        $this->assertStringContainsString(
            'This clinic has reached its maximum storage capacity. To continue uploading, please contact the platform Administrator to upgrade your plan or delete unnecessary files.',
            $html
        );
    }

    public function test_over_100_percent_still_renders_critical_state(): void
    {
        $html = $this->render(135.0);

        $this->assertStringContainsString('data-storage-state="critical"', $html);
        // Progress bar width must be clamped to 100% even when usage exceeds it.
        $this->assertStringContainsString('width: 100%', $html);
    }
}
