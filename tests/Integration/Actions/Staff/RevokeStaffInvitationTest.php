<?php

use App\Actions\Staff\RevokeStaffInvitation;
use App\Models\Staff\StaffInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('revokes a pending invitation', function () {
    $invitation = StaffInvitation::factory()->create(['accepted_at' => null]);

    resolve(RevokeStaffInvitation::class)($invitation->id);

    expect(StaffInvitation::query()->find($invitation->id))->toBeNull();
});

test('does not revoke accepted invitations', function () {
    $invitation = StaffInvitation::factory()->create(['accepted_at' => now()]);

    resolve(RevokeStaffInvitation::class)($invitation->id);

    expect(StaffInvitation::query()->find($invitation->id))->not->toBeNull();
});
