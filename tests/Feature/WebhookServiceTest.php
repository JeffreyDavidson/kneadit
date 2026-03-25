<?php

use App\Models\Setting;
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
    Setting::set('webhook_url', 'https://hooks.example.com/test');

    WebhookService::dispatch('order.created', ['order_number' => 'ORD-001']);

    Http::assertSentCount(1);
});

test('dispatch includes event header', function () {
    Http::fake(['*' => Http::response('ok', 200)]);
    Setting::set('webhook_url', 'https://hooks.example.com/test');

    WebhookService::dispatch('order.updated', ['status' => 'delivered']);

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-KneadIt-Event', 'order.updated');
    });
});

test('dispatch includes signature header', function () {
    Http::fake(['*' => Http::response('ok', 200)]);
    Setting::set('webhook_url', 'https://hooks.example.com/test');
    Setting::set('webhook_secret', 'my-secret');

    WebhookService::dispatch('order.created', ['test' => true]);

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-KneadIt-Signature');
    });
});

test('dispatch body contains event and data', function () {
    Http::fake(['*' => Http::response('ok', 200)]);
    Setting::set('webhook_url', 'https://hooks.example.com/test');

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
    Setting::set('webhook_url', 'https://hooks.example.com/test');

    // Should not throw
    WebhookService::dispatch('order.created', ['test' => true]);

    expect(true)->toBeTrue();
});

test('order actions dispatch webhooks', function () {
    $transitionSource = file_get_contents(app_path('Actions/Orders/TransitionOrderStatus.php'));
    $createSource = file_get_contents(app_path('Actions/Orders/CreateOrder.php'));

    expect($transitionSource)->toContain('WebhookService::dispatch')
        ->and($transitionSource)->toContain('order.updated')
        ->and($createSource)->toContain('WebhookService::dispatch')
        ->and($createSource)->toContain('order.created');
});
