<?php

use App\Enums\Orders\OrderStatus;
use App\Services\Platform\WebhookService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    setUpCentralTest();
});

test('dispatch does nothing without webhook url', function () {
    Http::preventStrayRequests();
    Http::fake();

    resolve(WebhookService::class)->dispatch('order.created', ['test' => true]);

    Http::assertNothingSent();
});

test('dispatch sends to configured url', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('ok', 200)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);
    settings(['webhook_secret' => 'test-secret']);

    resolve(WebhookService::class)->dispatch('order.created', ['order_number' => 'ORD-001']);

    Http::assertSentCount(1);
});

test('dispatch includes event header', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('ok', 200)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);
    settings(['webhook_secret' => 'test-secret']);

    resolve(WebhookService::class)->dispatch('order.updated', ['status' => OrderStatus::Delivered]);

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-KneadIt-Event', 'order.updated');
    });
});

test('dispatch includes signature header', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('ok', 200)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);
    settings(['webhook_secret' => 'my-secret']);

    resolve(WebhookService::class)->dispatch('order.created', ['test' => true]);

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-KneadIt-Signature');
    });
});

test('dispatch body contains event and data', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('ok', 200)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);
    settings(['webhook_secret' => 'test-secret']);

    resolve(WebhookService::class)->dispatch('order.created', ['order_number' => 'ORD-001']);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        if (! is_array($body)) {
            return false;
        }

        $data = $body['data'] ?? null;

        return $body['event'] === 'order.created'
            && is_array($data)
            && ($data['order_number'] ?? null) === 'ORD-001'
            && isset($body['timestamp']);
    });
});

test('dispatch handles failed request gracefully', function () {
    Http::preventStrayRequests();
    Http::fake(['*' => Http::response('error', 500)]);
    settings(['webhook_url' => 'https://hooks.example.com/test']);
    settings(['webhook_secret' => 'test-secret']);

    // Should not throw
    resolve(WebhookService::class)->dispatch('order.created', ['test' => true]);

    expect(true)->toBeTrue();
});

test('dispatch does nothing when url is set but secret is missing', function () {
    Http::preventStrayRequests();
    Http::fake();
    settings(['webhook_url' => 'https://hooks.example.com/test']);

    resolve(WebhookService::class)->dispatch('order.created', ['test' => true]);

    Http::assertNothingSent();
});

test('webhook dispatchers call WebhookService', function () {
    $createdSource = file_get_contents(app_path('Listeners/Orders/DispatchOrderCreatedWebhookListener.php'));
    $updatedSource = file_get_contents(app_path('Listeners/Orders/DispatchOrderWebhookListener.php'));

    expect($createdSource)->toContain('webhookService->dispatch')
        ->and($createdSource)->toContain('order.created')
        ->and($updatedSource)->toContain('webhookService->dispatch')
        ->and($updatedSource)->toContain('order.updated');
});
