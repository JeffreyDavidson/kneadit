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

test('capacity endpoint rejects a date that is not a real Y-m-d date with 422', function (string $date) {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson("/api/capacity/{$date}");

    $response->assertUnprocessable();
})->with([
    'garbage' => 'garbage',
    'wrong format' => '10-05-2026',
    'impossible date' => '2026-02-31',
]);
