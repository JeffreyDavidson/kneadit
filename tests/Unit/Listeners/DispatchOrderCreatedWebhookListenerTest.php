<?php

use App\Events\Orders\OrderCreated;
use App\Listeners\Orders\DispatchOrderCreatedWebhookListener;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it dispatches order.created webhook with order data', function () {
    Http::fake();

    settings(['webhook_url' => 'https://hooks.example.com/test']);
    settings(['webhook_secret' => 'test-secret']);

    $order = Order::factory()->create();
    $order->loadMissing('orderItems.product');

    $event = new OrderCreated($order);

    $listener = new DispatchOrderCreatedWebhookListener;
    $listener->handle($event);

    Http::assertSent(function ($request) use ($order) {
        $body = json_decode($request->body(), true);

        return $request->hasHeader('X-KneadIt-Event', 'order.created')
            && $body['event'] === 'order.created'
            && $body['data']['order_number'] === $order->order_number;
    });
});
