<?php

use App\Models\Orders\Order;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('order confirmation controller passes settings and content to view', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('order.confirmation', ['order' => $order->order_number], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('journeySteps');
});
