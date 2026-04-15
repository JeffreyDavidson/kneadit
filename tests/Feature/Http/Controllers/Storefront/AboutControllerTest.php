<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('about controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.about', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});
