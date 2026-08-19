<?php

use App\Enums\Staff\UserRole;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use Illuminate\Support\Str;

beforeEach(fn () => setUpTenantTest());

test('staff invitation model exists', function () {
    expect(class_exists(StaffInvitation::class))->toBeTrue();
});

test('invitation can be created', function () {
    $owner = User::factory()->owner()->create();
    StaffInvitation::factory()->create([
        'email' => 'staff@example.com',
        'role' => UserRole::Staff,
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'invited_by' => $owner->id,
    ]);

    $this->assertDatabaseHas('staff_invitations', ['email' => 'staff@example.com']);
});

test('invitation belongs to inviter', function () {
    $owner = User::factory()->owner()->create();
    $invitation = StaffInvitation::factory()->create([
        'email' => 'staff@example.com',
        'role' => UserRole::Staff,
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'invited_by' => $owner->id,
    ]);

    expect($invitation->inviter()->firstOrFail()->id)->toBe($owner->id);
});
