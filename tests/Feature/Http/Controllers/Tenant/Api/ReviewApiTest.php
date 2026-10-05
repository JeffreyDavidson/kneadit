<?php

use App\Models\Engagement\Review;
use App\Models\Inventory\Product;

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

test('reviews include exposes active products but hides inactive ones', function () {
    $active = Product::factory()->active()->create();
    $inactive = Product::factory()->inactive()->create();
    $activeReview = Review::factory()->approved()->for($active)->create();
    $inactiveReview = Review::factory()->approved()->for($inactive)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/reviews?include=product');

    $response->assertOk()->assertJsonCount(2, 'data');

    $data = collect($response->json('data'))->keyBy('id');

    expect(collect($response->json('included'))->pluck('id')->all())->toBe([(string) $active->id])
        ->and($response->getContent())->not->toContain($inactive->name)
        ->and($data[(string) $activeReview->id]['relationships']['product']['data']['id'])->toBe((string) $active->id)
        ->and($data[(string) $inactiveReview->id]['relationships']['product']['data'])->toBeNull();
});

test('reviews cannot be submitted through the API', function () {
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->postJson('/api/reviews', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Absolutely delicious!',
        ]);

    $response->assertStatus(405);

    test()->assertDatabaseCount('reviews', 0);
});
