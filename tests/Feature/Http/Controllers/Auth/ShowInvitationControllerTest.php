<?php

use App\Models\Staff\StaffInvitation;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('it shows a pending invitation', function () {
    $invitation = StaffInvitation::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('invitation.show', $invitation->token, false));

    $response->assertOk()
        ->assertViewIs('invitations.show')
        ->assertViewHas('invitation');
});
