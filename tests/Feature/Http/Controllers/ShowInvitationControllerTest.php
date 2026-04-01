<?php

use App\Models\Staff\StaffInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it shows a pending invitation', function () {
    $invitation = StaffInvitation::factory()->create();

    $response = $this
        ->withoutMiddleware(tenantMiddleware())
        ->get(route('invitation.show', $invitation->token));

    $response->assertOk()
        ->assertViewIs('invitations.show')
        ->assertViewHas('invitation');
});
