<?php

use App\Models\StaffInvitation;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('invitation page renders for valid token', function () {
    $invitation = StaffInvitation::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get("/invite/{$invitation->token}");

    $response->assertOk();
});
