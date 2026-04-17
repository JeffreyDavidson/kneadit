<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('capacity endpoint returns availability for a date as JSON:API', function () {
    $date = now()->addDays(5)->toDateString();

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/api/capacity/{$date}");

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.api+json')
        ->assertJsonPath('data.type', 'capacity-slots')
        ->assertJsonPath('data.id', $date)
        ->assertJsonStructure([
            'data' => ['id', 'type', 'attributes' => ['available', 'remaining', 'max']],
        ]);
});
