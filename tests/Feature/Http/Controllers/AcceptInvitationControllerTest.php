<?php

use App\Models\StaffInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it accepts a pending invitation and redirects to admin', function () {
    $invitation = StaffInvitation::factory()->create();

    $response = $this
        ->withoutMiddleware(tenantMiddleware())
        ->post(route('invitation.accept', $invitation->token), [
            'name' => 'New Staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect('/admin');
});
