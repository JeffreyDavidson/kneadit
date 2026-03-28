<?php

use App\Models\Product;
use App\Models\Review;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('reviews index returns approved reviews', function () {
    Review::factory()->count(2)->create(['is_approved' => true]);
    Review::factory()->create(['is_approved' => false]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/reviews');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('reviews store creates a pending review', function () {
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/reviews', [
            'customer_name' => 'Alice',
            'customer_email' => 'alice@example.com',
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Amazing sourdough!',
        ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Review submitted and pending approval.');

    expect(Review::query()->first())
        ->is_approved->toBeFalse()
        ->rating->toBe(5);
});
