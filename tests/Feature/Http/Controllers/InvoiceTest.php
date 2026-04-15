<?php

use App\Models\Orders\Order;
use App\Models\Staff\User;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('invoice page renders for an order', function () {
    $this->actingAs(User::factory()->owner()->create());
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('admin.orders.invoice', $order, false));

    $response->assertOk();
});
