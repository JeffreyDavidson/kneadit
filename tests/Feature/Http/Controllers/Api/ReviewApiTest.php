<?php

use App\Models\Engagement\Review;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('reviews endpoint returns approved reviews', function () {
    Review::factory()->count(2)->create(['is_approved' => true]);
    Review::factory()->pending()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/reviews');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('reviews endpoint filters by featured when requested', function () {
    Review::factory()->approved()->create(['is_featured' => false]);
    Review::factory()->featured()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/reviews?featured=1');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

test('reviews endpoint returns all approved when featured is not requested', function () {
    Review::factory()->approved()->create(['is_featured' => false]);
    Review::factory()->featured()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/reviews');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('store review creates review and returns 201', function () {
    $product = App\Models\Inventory\Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/reviews', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Absolutely delicious!',
        ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Review submitted and pending approval.');

    test()->assertDatabaseHas('reviews', [
        'customer_name' => 'Jane Doe',
        'customer_email' => 'jane@example.com',
        'product_id' => $product->id,
        'rating' => 5,
        'is_approved' => false,
    ]);
});

test('store review fails validation with missing fields', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/reviews', []);

    $response->assertUnprocessable();

    $pointers = collect($response->json('errors'))->pluck('source.pointer')->all();
    expect($pointers)->toContain(
        '/data/attributes/customer_name',
        '/data/attributes/customer_email',
        '/data/attributes/product_id',
        '/data/attributes/rating',
        '/data/attributes/comment',
    );
});

test('store review fails validation with invalid rating', function () {
    $product = App\Models\Inventory\Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/reviews', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'product_id' => $product->id,
            'rating' => 6,
            'comment' => 'Great!',
        ]);

    $response->assertUnprocessable()
        ->assertJsonPath('errors.0.source.pointer', '/data/attributes/rating');
});
