<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderCancelled;
use App\Listeners\Orders\DispatchOrderCancelledWebhookListener;
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

test('it dispatches order.cancelled webhook with order data', function () {
    settings(['webhook_url' => 'https://8.8.8.8/test']);
    settings(['webhook_secret' => 'test-secret']);

    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    resolve(DispatchOrderCancelledWebhookListener::class)->handle(
        new OrderCancelled($order, OrderStatus::Baking),
    );

    Http::assertSent(function ($request) use ($order) {
        $body = json_decode($request->body(), true);

        return $request->hasHeader('X-KneadIt-Event', 'order.cancelled')
            && $body['event'] === 'order.cancelled'
            && $body['data']['order_number'] === $order->order_number
            && $body['data']['previous_status'] === 'baking';
    });
});

test('it does not dispatch webhook when no webhook url is configured', function () {
    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    resolve(DispatchOrderCancelledWebhookListener::class)->handle(
        new OrderCancelled($order, OrderStatus::Pending),
    );

    Http::assertNothingSent();
});

test('failed method logs a warning with order number and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('Order cancelled webhook dispatch failed', Mockery::on(fn (array $context) => $context['order'] === 'ORD-CANCEL-001'
            && $context['error'] === 'Connection refused'));

    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create(['order_number' => 'ORD-CANCEL-001']);
    $event = new OrderCancelled($order, OrderStatus::Baking);

    resolve(DispatchOrderCancelledWebhookListener::class)->failed($event, new RuntimeException('Connection refused'));
});
