<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('capacity check returns availability for a date', function () {
    $date = now()->addDays(5)->toDateString();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/capacity/check/{$date}");

    $response->assertOk()
        ->assertJsonStructure(['available', 'remaining', 'max_orders', 'usage_percent']);
});
