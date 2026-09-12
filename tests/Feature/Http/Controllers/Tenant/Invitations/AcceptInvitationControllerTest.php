<?php

use App\Models\Staff\StaffInvitation;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('it accepts a pending invitation and redirects to admin', function () {
    $invitation = StaffInvitation::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('invitation.accept', $invitation->token, false), [
            'name' => 'New Staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect('/admin');
});
