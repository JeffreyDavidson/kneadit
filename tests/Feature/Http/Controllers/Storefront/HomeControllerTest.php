<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('storefront home page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/');

    $response->assertOk();
});

test('marketing home page renders its navigation and hero', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/');

    $response->assertOk()
        ->assertSee('site-nav', false)
        ->assertSee('hero', false)
        ->assertSee('Your bakery.', false);
});
