<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('store info endpoint returns store data', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/store');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['store_name', 'colors', 'social_links'],
            'message',
        ]);
});
