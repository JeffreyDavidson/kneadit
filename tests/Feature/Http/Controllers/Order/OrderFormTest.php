<?php

use App\Models\Orders\Order;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('order form page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/order');

    $response->assertOk();
});

test('order confirmation page renders', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/order/confirmation/{$order->order_number}");

    $response->assertOk();
});
