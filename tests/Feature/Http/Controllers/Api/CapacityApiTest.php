<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('capacity endpoint returns availability for a date', function () {
    $date = now()->addDays(5)->toDateString();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/api/capacity/{$date}");

    $response->assertOk()
        ->assertJsonStructure(['data' => ['available', 'remaining', 'max'], 'message']);
});
