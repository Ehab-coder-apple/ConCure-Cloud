<?php

namespace Tests\Unit;

use App\Services\WhatsAppService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Regression test: a clinic that has WPPConnect selected as its provider
 * (with a real api_key configured) but whose WPPConnect server is not
 * actually reachable (e.g. "cURL error 7: Failed to connect to localhost
 * port 21465") used to receive a hard failure with the raw connection
 * error, leaving no way to send the reminder. It should instead fall back
 * to the web (wa.me) click-to-chat link, same as every other unreachable/
 * misconfigured provider.
 */
class WhatsAppServiceWppconnectFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        // Force the app to behave as if it is NOT running in console for this
        // test, since sendViaWebWhatsApp() intentionally refuses to produce a
        // wa.me fallback link in a CLI/scheduled context (no browser to open
        // it in). Real HTTP requests always have this false.
        putenv('APP_RUNNING_IN_CONSOLE=false');

        parent::setUp();

        Config::set('whatsapp.providers.wppconnect.api_url', 'http://localhost:21465');
        Config::set('whatsapp.providers.wppconnect.api_key', 'configured-key');
        Config::set('whatsapp.providers.wppconnect.session_name', 'clinic_5');
        Config::set('whatsapp.default_provider', 'wppconnect');
    }

    protected function tearDown(): void
    {
        putenv('APP_RUNNING_IN_CONSOLE');
        parent::tearDown();
    }

    private function callSendViaWppconnect(WhatsAppService $service, string $phone, string $message): array
    {
        $method = new ReflectionMethod($service, 'sendViaWPPConnect');
        $method->setAccessible(true);

        return $method->invoke($service, $phone, $message, null);
    }

    public function test_falls_back_to_web_whatsapp_link_when_wppconnect_server_is_unreachable(): void
    {
        Http::fake(function () {
            throw new ConnectionException('cURL error 7: Failed to connect to localhost port 21465 after 0 ms');
        });

        $service = new WhatsAppService();

        $result = $this->callSendViaWppconnect($service, '9647701234567', 'Hello there');

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('whatsapp_url', $result);
        $this->assertStringContainsString('wa.me/9647701234567', $result['whatsapp_url']);
        $this->assertStringContainsString('cURL error 7', $result['error']);
    }

    public function test_falls_back_to_web_whatsapp_link_when_wppconnect_session_is_disconnected(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'Session not found'], 422),
        ]);

        $service = new WhatsAppService();

        $result = $this->callSendViaWppconnect($service, '9647701234567', 'Hello there');

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('whatsapp_url', $result);
        $this->assertTrue($result['setup_required']);
    }
}
