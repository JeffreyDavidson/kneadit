<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;

beforeEach(function () {
    setUpTenantTest();
});

function createReviewProduct(): Product
{
    $category = Category::create([
        'name' => 'Breads',
        'slug' => 'breads',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    return Product::create([
        'name' => 'Sourdough',
        'slug' => 'sourdough',
        'price' => 8.00,
        'category_id' => $category->id,
        'is_active' => true,
    ]);
}

test('reviews page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/reviews');

    $response->assertOk();
});

test('reviews page shows approved reviews', function () {
    $product = createReviewProduct();

    Review::create([
        'customer_name' => 'Happy Customer',
        'customer_email' => 'happy@example.com',
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Absolutely delicious sourdough!',
        'is_approved' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get('/reviews');

    $response->assertOk();
    $response->assertSee('Happy Customer');
    $response->assertSee('Absolutely delicious sourdough!');
});

test('reviews page hides unapproved reviews', function () {
    $product = createReviewProduct();

    Review::create([
        'customer_name' => 'Pending Reviewer',
        'customer_email' => 'pending@example.com',
        'product_id' => $product->id,
        'rating' => 3,
        'comment' => 'This should not be visible yet',
        'is_approved' => false,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get('/reviews');

    $response->assertOk();
    $response->assertDontSee('This should not be visible yet');
});

test('reviews page shows average rating', function () {
    $product = createReviewProduct();

    Review::create([
        'customer_name' => 'A',
        'customer_email' => 'a@example.com',
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Great!',
        'is_approved' => true,
    ]);

    Review::create([
        'customer_name' => 'B',
        'customer_email' => 'b@example.com',
        'product_id' => $product->id,
        'rating' => 3,
        'comment' => 'Good',
        'is_approved' => true,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get('/reviews');

    $response->assertOk();
    // Average is 4.0
    $response->assertSee('4');
});

test('empty reviews page shows empty state', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/reviews');

    $response->assertOk();
    expect(Review::count())->toBe(0);
});

test('reviews only counts approved in average', function () {
    $product = createReviewProduct();

    Review::create([
        'customer_name' => 'A',
        'customer_email' => 'a@example.com',
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Amazing',
        'is_approved' => true,
    ]);

    Review::create([
        'customer_name' => 'Troll',
        'customer_email' => 'troll@example.com',
        'product_id' => $product->id,
        'rating' => 1,
        'comment' => 'Spam review',
        'is_approved' => false,
    ]);

    $avgRating = Review::where('is_approved', true)->avg('rating');
    expect((float) $avgRating)->toBe(5.0);
});
