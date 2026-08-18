<?php

use App\Events\Orders\OrderCreated;
use App\Listeners\Orders\DispatchOrderCreatedWebhookListener;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it dispatches order.created webhook with order data', function () {
    Http::fake();

    settings(['webhook_url' => 'https://8.8.8.8/test']);
    settings(['webhook_secret' => 'test-secret']);

    $order = Order::factory()->create();
    $order->loadMissing('orderItems.product');

    $event = new OrderCreated($order);

    $listener = resolve(DispatchOrderCreatedWebhookListener::class);
    $listener->handle($event);

    Http::assertSent(function ($request) use ($order) {
        $body = json_decode($request->body(), true);

        return $request->hasHeader('X-KneadIt-Event', 'order.created')
            && $body['event'] === 'order.created'
            && $body['data']['order_number'] === $order->order_number;
    });
});

test('it does not dispatch webhook when no webhook url is configured', function () {
    Http::fake();

    settings(['webhook_url' => '']);

    $order = Order::factory()->create();
    $event = new OrderCreated($order);

    $listener = resolve(DispatchOrderCreatedWebhookListener::class);
    $listener->handle($event);

    Http::assertNothingSent();
});

test('failed method logs a warning with order number and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('Order created webhook dispatch failed', Mockery::on(fn (array $context) => $context['order'] === 'ORD-001'
            && $context['error'] === 'Connection refused'));

    $order = Order::factory()->create(['order_number' => 'ORD-001']);
    $event = new OrderCreated($order);

    $listener = resolve(DispatchOrderCreatedWebhookListener::class);
    $listener->failed($event, new RuntimeException('Connection refused'));
});
