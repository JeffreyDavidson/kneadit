<?php

use App\Models\Orders\Order;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('submit review controller passes settings to view', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.submitReview', $order->order_number, false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('ratingDescriptions');
});
