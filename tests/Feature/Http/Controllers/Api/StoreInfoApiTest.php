<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    settings([
        'store_name' => 'Sweet Sunrise Bakery',
        'email' => 'hello@sweetbakery.com',
        'phone' => '555-0123',
    ]);
});

test('store info endpoint returns store settings', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/api/store');

    $response->assertOk()
        ->assertJsonPath('data.store_name', 'Sweet Sunrise Bakery')
        ->assertJsonPath('data.email', 'hello@sweetbakery.com')
        ->assertJsonPath('data.phone', '555-0123')
        ->assertJsonStructure(['data' => ['store_name', 'colors', 'social_links'], 'message']);
});
