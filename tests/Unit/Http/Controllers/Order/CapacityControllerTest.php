<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it rejects an invalid date', function () {
    $response = $this
        ->withoutMiddleware(tenantMiddleware())
        ->getJson(route('capacity.check', 'not-a-date'));

    $response->assertUnprocessable();
});
