<?php

use App\Models\Orders\Order;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('submit review controller passes settings to view', function () {
    $order = Order::factory()->create();
    $signed = URL::temporarySignedRoute('storefront.submitReview', now()->addHour(), [
        'order' => $order->order_number,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get($signed);

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('ratingDescriptions');
});

test('submit review controller rejects unsigned requests', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.submitReview', $order->order_number, false));

    $response->assertForbidden();
});

test('submit review controller grants session access so the POST form passes the order.access gate', function () {
    $order = Order::factory()->create();
    $signed = URL::temporarySignedRoute('storefront.submitReview', now()->addHour(), [
        'order' => $order->order_number,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get($signed);

    $response->assertOk();
    expect(session('verified_order_numbers'))->toContain($order->order_number);
});
