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
