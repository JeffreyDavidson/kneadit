<?php

use App\Models\Engagement\Review;
use App\Models\Orders\Order;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('store review controller creates review and renders the success view', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->post(route('storefront.storeReview', $order->order_number, false), [
            'rating' => 5,
            'comment' => 'Best sourdough in town!',
        ]);

    $response->assertOk()
        ->assertViewHas('success', true)
        ->assertViewHas('order')
        ->assertViewHas('settings');

    expect(Review::query()->count())->toBe(1)
        ->and(Review::query()->first()->rating)->toBe(5);
});

test('store review controller validates rating is required', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->post(route('storefront.storeReview', $order->order_number, false), [
            'comment' => 'Missing rating',
        ]);

    $response->assertSessionHasErrors('rating');
    expect(Review::query()->count())->toBe(0);
});

test('store review controller validates rating is between 1 and 5', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->post(route('storefront.storeReview', $order->order_number, false), [
            'rating' => 10,
        ]);

    $response->assertSessionHasErrors('rating');
});
