<?php

use App\Actions\Operations\RedeliverWebhook;
use App\Models\Operations\WebhookDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['webhook_url' => 'https://8.8.8.8/test']);
    settings(['webhook_secret' => 'test-secret']);
});

test('redeliver re-fires the webhook with the original payload data', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    $original = WebhookDelivery::factory()->create([
        'event' => 'order.created',
        'payload' => [
            'event' => 'order.created',
            'timestamp' => '2026-01-01T00:00:00+00:00',
            'data' => ['order_number' => 'ORD-XYZ'],
        ],
    ]);

    resolve(RedeliverWebhook::class)($original);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return data_get($body, 'event') === 'order.created'
            && data_get($body, 'data.order_number') === 'ORD-XYZ';
    });

    expect(WebhookDelivery::count())->toBe(2);
});
