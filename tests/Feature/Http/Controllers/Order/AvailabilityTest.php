<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('availability endpoint returns 30 days of dates', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('order.availability', absolute: false));

    $response->assertOk()
        ->assertJsonCount(30, 'data');
});
