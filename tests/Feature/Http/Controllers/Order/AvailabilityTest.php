<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('availability endpoint returns 30 days of dates', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson('/availability');

    $response->assertOk()
        ->assertJsonCount(30);
});
