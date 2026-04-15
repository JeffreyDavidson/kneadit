<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('driver page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('driver.index', [], false));

    $response->assertOk();
});
