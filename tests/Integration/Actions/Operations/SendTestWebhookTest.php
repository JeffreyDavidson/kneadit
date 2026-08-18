<?php

use App\Actions\Operations\SendTestWebhook;
use App\Models\Operations\WebhookDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['webhook_url' => 'https://hooks.example.com/test']);
    settings(['webhook_secret' => 'test-secret']);
});

test('sends a synthetic order.created payload flagged with test:true', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    resolve(SendTestWebhook::class)();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return $request->hasHeader('X-KneadIt-Event', 'order.created')
            && $body['event'] === 'order.created'
            && $body['data']['test'] === true
            && $body['data']['order_number'] === 'TEST-0001';
    });

    expect(WebhookDelivery::sole())
        ->event->toBe('order.created')
        ->succeeded->toBeTrue();
});
