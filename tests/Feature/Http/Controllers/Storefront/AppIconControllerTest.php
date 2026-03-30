<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('app icon endpoint returns PNG image', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('app.icon', ['size' => '192'], false));

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});
