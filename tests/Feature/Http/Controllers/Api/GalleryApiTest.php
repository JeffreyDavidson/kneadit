<?php

use App\Models\GalleryPhoto;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('gallery endpoint returns visible photos', function () {
    GalleryPhoto::factory()->count(3)->create(['is_visible' => true]);
    GalleryPhoto::factory()->create(['is_visible' => false]);

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/gallery');

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});
