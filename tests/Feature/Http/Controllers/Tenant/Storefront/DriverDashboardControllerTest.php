<?php

use App\Models\Staff\User;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('driver page renders', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs(User::factory()->staff()->create())
        ->get(route('driver.index', [], false));

    $response->assertOk();
});
