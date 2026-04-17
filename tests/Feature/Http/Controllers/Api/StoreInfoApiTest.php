<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('store info endpoint returns store data in JSON:API format', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/store');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonStructure([
            'data' => [
                'id',
                'type',
                'attributes' => ['store_name', 'colors', 'social_links'],
            ],
        ])
        ->assertJsonPath('data.type', 'store-info');
});
