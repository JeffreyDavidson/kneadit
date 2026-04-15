<?php

use App\Models\Staff\StaffInvitation;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('invitation page renders for valid token', function () {
    $invitation = StaffInvitation::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('invitation.show', $invitation->token, false));

    $response->assertOk();
});
