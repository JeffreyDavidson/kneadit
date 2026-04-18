<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\Orders\DispatchOrderWebhookListener;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    Http::fake();
    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create(['email' => 'buyer@example.com']);
});

test('dispatches webhook with transition payload', function () {
    settings(['webhook_url' => 'https://hooks.example.com/test']);

    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    resolve(DispatchOrderWebhookListener::class)->handle(
        new OrderStatusChanged($order, OrderStatus::Pending, OrderStatus::Confirmed),
    );

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return $request->hasHeader('X-KneadIt-Event', 'order.updated')
            && $body['data']['status'] === 'confirmed'
            && $body['data']['previous_status'] === 'pending';
    });
});

test('no request when webhook_url is not configured', function () {
    $order = Order::factory()->for(test()->customer)->recycle(test()->user)->create();

    resolve(DispatchOrderWebhookListener::class)->handle(
        new OrderStatusChanged($order, OrderStatus::Pending, OrderStatus::Confirmed),
    );

    Http::assertNothingSent();
});
