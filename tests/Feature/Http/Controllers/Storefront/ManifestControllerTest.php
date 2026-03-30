<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('manifest endpoint returns valid JSON manifest', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->getJson(route('manifest', [], false));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/manifest+json')
        ->assertJsonStructure(['name', 'short_name', 'start_url', 'display', 'icons']);
});
