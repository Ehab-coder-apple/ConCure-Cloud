<?php

namespace Tests\Unit;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Config;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Regression test for the "WhatsApp send fails" bug on follow-up reminders
 * (and any other WhatsApp feature): WPPConnect's api_url config always has
 * a non-empty default ('http://localhost:21465'), so the provider
 * auto-detection was treating WPPConnect as "configured" and routing every
 * send through it — even for clinics that never set up WPPConnect (no
 * api_key) — instead of falling back to the web (wa.me) click-to-chat
 * link. This caused every send to hard-fail with
 * "WPPConnect is not configured..." rather than succeeding via the
 * web fallback.
 */
class WhatsAppServiceProviderSelectionTest extends TestCase
{
    private function getSelectedProvider(WhatsAppService $service): string
    {
        $property = new ReflectionProperty($service, 'provider');
        $property->setAccessible(true);

        return $property->getValue($service);
    }

    public function test_falls_back_to_web_when_wppconnect_has_no_api_key_configured(): void
    {
        // No Twilio/Meta/ChatAPI credentials configured (default test env).
        // WPPConnect's api_url keeps its shipped default, but no api_key
        // is set — this is the state of an unconfigured clinic.
        Config::set('whatsapp.providers.wppconnect.api_key', null);

        $service = new WhatsAppService();

        $this->assertSame('web', $this->getSelectedProvider($service));
    }

    public function test_uses_wppconnect_once_an_api_key_is_actually_configured(): void
    {
        Config::set('whatsapp.providers.wppconnect.api_key', 'real-api-key');

        $service = new WhatsAppService();

        $this->assertSame('wppconnect', $this->getSelectedProvider($service));
    }
}
