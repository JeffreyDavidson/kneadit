<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\Orders\DispatchOrderUpdatedWebhookListener;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it dispatches order.updated webhook with status change data', function () {
    Http::fake();

    settings(['webhook_url' => 'https://hooks.example.com/test']);
    settings(['webhook_secret' => 'test-secret']);

    $order = Order::factory()->create(['status' => OrderStatus::Confirmed]);

    $event = new OrderStatusChanged($order, OrderStatus::Pending, OrderStatus::Confirmed);

    $listener = new DispatchOrderUpdatedWebhookListener;
    $listener->handle($event);

    Http::assertSent(function ($request) use ($order) {
        $body = json_decode($request->body(), true);

        return $request->hasHeader('X-KneadIt-Event', 'order.updated')
            && $body['event'] === 'order.updated'
            && $body['data']['order_number'] === $order->order_number
            && $body['data']['status'] === 'confirmed'
            && $body['data']['previous_status'] === 'pending';
    });
});
