<?php

use App\Enums\OrderStatus;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    setUpCentralTest();
});

test('dispatch does nothing without webhook url', function () {
    Http::fake();

    WebhookService::dispatch('order.created', ['test' => true]);

    Http::assertNothingSent();
});

test('dispatch sends to configured url', function () {
    Http::fake(['*' => Http::response('ok', 200)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);

    WebhookService::dispatch('order.created', ['order_number' => 'ORD-001']);

    Http::assertSentCount(1);
});

test('dispatch includes event header', function () {
    Http::fake(['*' => Http::response('ok', 200)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);

    WebhookService::dispatch('order.updated', ['status' => OrderStatus::Delivered]);

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-KneadIt-Event', 'order.updated');
    });
});

test('dispatch includes signature header', function () {
    Http::fake(['*' => Http::response('ok', 200)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);
    settings(['webhook_secret' => 'my-secret']);

    WebhookService::dispatch('order.created', ['test' => true]);

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-KneadIt-Signature');
    });
});

test('dispatch body contains event and data', function () {
    Http::fake(['*' => Http::response('ok', 200)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);

    WebhookService::dispatch('order.created', ['order_number' => 'ORD-001']);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return $body['event'] === 'order.created'
            && $body['data']['order_number'] === 'ORD-001'
            && isset($body['timestamp']);
    });
});

test('dispatch handles failed request gracefully', function () {
    Http::fake(['*' => Http::response('error', 500)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);

    // Should not throw
    WebhookService::dispatch('order.created', ['test' => true]);

    expect(true)->toBeTrue();
});

test('webhook listeners dispatch webhooks', function () {
    $createdSource = file_get_contents(app_path('Listeners/DispatchOrderCreatedWebhook.php'));
    $updatedSource = file_get_contents(app_path('Listeners/DispatchOrderUpdatedWebhook.php'));

    expect($createdSource)->toContain('WebhookService::dispatch')
        ->and($createdSource)->toContain('order.created')
        ->and($updatedSource)->toContain('WebhookService::dispatch')
        ->and($updatedSource)->toContain('order.updated');
});
