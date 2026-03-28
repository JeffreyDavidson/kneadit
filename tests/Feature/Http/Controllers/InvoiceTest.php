<?php

use App\Models\Order;
use App\Models\User;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('invoice page renders for an order', function () {
    $this->actingAs(User::factory()->owner()->create());
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/admin/orders/{$order->order_number}/invoice");

    $response->assertOk();
});
