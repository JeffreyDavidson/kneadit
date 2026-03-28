<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('driver page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get('/driver');

    $response->assertOk();
});
