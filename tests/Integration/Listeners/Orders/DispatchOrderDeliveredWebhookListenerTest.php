<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderDelivered;
use App\Listeners\Orders\DispatchOrderDeliveredWebhookListener;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    Http::fake();
    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create(['email' => 'buyer@example.com']);
});

test('it dispatches order.delivered webhook with order data', function () {
    settings(['webhook_url' => 'https://8.8.8.8/test']);
    settings(['webhook_secret' => 'test-secret']);

    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    resolve(DispatchOrderDeliveredWebhookListener::class)->handle(
        new OrderDelivered($order, OrderStatus::Ready),
    );

    Http::assertSent(function ($request) use ($order) {
        $body = json_decode($request->body(), true);

        return $request->hasHeader('X-KneadIt-Event', 'order.delivered')
            && $body['event'] === 'order.delivered'
            && $body['data']['order_number'] === $order->order_number
            && $body['data']['previous_status'] === 'ready';
    });
});

test('it does not dispatch webhook when no webhook url is configured', function () {
    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    resolve(DispatchOrderDeliveredWebhookListener::class)->handle(
        new OrderDelivered($order, OrderStatus::Ready),
    );

    Http::assertNothingSent();
});

test('failed method logs a warning with order number and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'Order delivered webhook dispatch failed'
            && $context['order'] === 'ORD-DELIV-001'
            && $context['error'] === 'Connection refused');

    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create(['order_number' => 'ORD-DELIV-001']);
    $event = new OrderDelivered($order, OrderStatus::Ready);

    resolve(DispatchOrderDeliveredWebhookListener::class)->failed($event, new RuntimeException('Connection refused'));
});
