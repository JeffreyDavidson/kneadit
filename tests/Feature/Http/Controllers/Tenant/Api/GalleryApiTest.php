<?php

use App\Models\Content\GalleryPhoto;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('gallery endpoint returns visible photos as JSON:API', function () {
    GalleryPhoto::factory()->count(3)->create(['is_visible' => true]);
    GalleryPhoto::factory()->hidden()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/gallery');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.type', 'gallery-photos')
        ->assertJsonStructure([
            'data' => [
                ['id', 'type', 'attributes' => ['title', 'image_path', 'category']],
            ],
        ]);
});
