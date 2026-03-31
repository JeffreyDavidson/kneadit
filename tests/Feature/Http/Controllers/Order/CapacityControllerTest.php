<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('capacity check returns availability for valid date', function () {
    $date = now()->addDays(3)->format('Y-m-d');

    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('capacity.check', ['date' => $date], false));

    $response->assertOk()
        ->assertJsonStructure(['data' => ['available', 'remaining', 'max_orders', 'usage_percent']]);
});

test('capacity check returns error for invalid date', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('capacity.check', ['date' => 'not-a-date'], false));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['date']);
});
