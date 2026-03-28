<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('order tracking page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/track');

    $response->assertOk();
});
