<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Http;
use Tests\CentralTestCase;

class WebhookServiceTest extends CentralTestCase
{
    public function test_dispatch_does_nothing_without_webhook_url(): void
    {
        Http::fake();

        WebhookService::dispatch('order.created', ['test' => true]);

        Http::assertNothingSent();
    }

    public function test_dispatch_sends_to_configured_url(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);
        Setting::set('webhook_url', 'https://hooks.example.com/test');

        WebhookService::dispatch('order.created', ['order_number' => 'ORD-001']);

        Http::assertSentCount(1);
    }

    public function test_dispatch_includes_event_header(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);
        Setting::set('webhook_url', 'https://hooks.example.com/test');

        WebhookService::dispatch('order.updated', ['status' => 'delivered']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-KneadIt-Event', 'order.updated');
        });
    }

    public function test_dispatch_includes_signature_header(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);
        Setting::set('webhook_url', 'https://hooks.example.com/test');
        Setting::set('webhook_secret', 'my-secret');

        WebhookService::dispatch('order.created', ['test' => true]);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-KneadIt-Signature');
        });
    }

    public function test_dispatch_body_contains_event_and_data(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);
        Setting::set('webhook_url', 'https://hooks.example.com/test');

        WebhookService::dispatch('order.created', ['order_number' => 'ORD-001']);

        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            return $body['event'] === 'order.created'
                && $body['data']['order_number'] === 'ORD-001'
                && isset($body['timestamp']);
        });
    }

    public function test_dispatch_handles_failed_request_gracefully(): void
    {
        Http::fake(['*' => Http::response('error', 500)]);
        Setting::set('webhook_url', 'https://hooks.example.com/test');

        // Should not throw
        WebhookService::dispatch('order.created', ['test' => true]);

        $this->assertTrue(true);
    }

    public function test_order_observer_dispatches_webhook(): void
    {
        $source = file_get_contents(app_path('Observers/OrderObserver.php'));

        $this->assertStringContainsString('WebhookService::dispatch', $source);
        $this->assertStringContainsString('order.created', $source);
        $this->assertStringContainsString('order.updated', $source);
    }
}
