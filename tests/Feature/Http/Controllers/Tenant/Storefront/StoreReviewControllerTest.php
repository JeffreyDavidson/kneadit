<?php

use App\Models\Engagement\Review;
use App\Models\Orders\Order;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('store review controller creates the review and redirects to the thank-you page', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->post(route('storefront.storeReview', $order->order_number, false), [
            'rating' => 5,
            'comment' => 'Best sourdough in town!',
        ]);

    $response->assertRedirect(route('storefront.reviewSubmitted', $order->order_number, false));

    expect(Review::query()->count())->toBe(1)
        ->and(Review::query()->first()->rating)->toBe(5);
});

test('the thank-you page renders the success view and can be refreshed without re-posting', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->get(route('storefront.reviewSubmitted', $order->order_number, false));

    $response->assertOk()
        ->assertViewHas('success', true)
        ->assertViewHas('order')
        ->assertViewHas('settings');
    expect(Review::query()->count())->toBe(0);
});

test('the thank-you page needs access to the order', function () {
    $order = Order::factory()->create();

    withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.reviewSubmitted', $order->order_number, false))
        ->assertRedirect(route('order.verify.show', $order->order_number, false));
});

test('posting the review form twice for one order keeps a single review', function () {
    $order = Order::factory()->create();
    $post = fn (int $rating) => withoutMiddleware(tenantMiddleware())
        ->withSession(verifiedOrdersSession([$order]))
        ->post(route('storefront.storeReview', $order->order_number, false), ['rating' => $rating]);

    $post(5);
    $post(2);

    expect(Review::query()->count())->toBe(1)
        ->and(Review::query()->first()->rating)->toBe(2);
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
