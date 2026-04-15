<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('availability endpoint returns json array', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('order.availability', [], false));

    $response->assertOk()
        ->assertJsonIsArray('data');
});

test('availability endpoint returns 30 days of dates', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('order.availability', [], false));

    $response->assertOk()
        ->assertJsonCount(30, 'data');
});
