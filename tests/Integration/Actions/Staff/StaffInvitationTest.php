<?php

use App\Enums\UserRole;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(fn () => setUpTenantTest());

test('staff invitation model exists', function () {
    expect(class_exists(StaffInvitation::class))->toBeTrue();
});

test('invitation can be created', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    StaffInvitation::factory()->create([
        'email' => 'staff@example.com',
        'role' => UserRole::Staff,
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'invited_by' => $owner->id,
    ]);

    $this->assertDatabaseHas('staff_invitations', ['email' => 'staff@example.com']);
});

test('invitation is expired when past expiry', function () {
    $invitation = StaffInvitation::factory()->create([
        'email' => 'staff@example.com',
        'role' => UserRole::Staff,
        'token' => Str::random(32),
        'expires_at' => now()->subDay(),
        'invited_by' => User::factory()->create(['role' => UserRole::Owner])->id,
    ]);

    expect($invitation->is_expired)->toBeTrue();
});

test('invitation is pending when not accepted and not expired', function () {
    $invitation = StaffInvitation::factory()->create([
        'email' => 'staff@example.com',
        'role' => UserRole::Staff,
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'invited_by' => User::factory()->create(['role' => UserRole::Owner])->id,
    ]);

    expect($invitation->is_pending)->toBeTrue();
});

test('invitation is not pending when accepted', function () {
    $invitation = StaffInvitation::factory()->create([
        'email' => 'staff@example.com',
        'role' => UserRole::Staff,
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'accepted_at' => now(),
        'invited_by' => User::factory()->create(['role' => UserRole::Owner])->id,
    ]);

    expect($invitation->is_pending)->toBeFalse();
});

test('invitation belongs to inviter', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $invitation = StaffInvitation::factory()->create([
        'email' => 'staff@example.com',
        'role' => UserRole::Staff,
        'token' => Str::random(32),
        'expires_at' => now()->addDays(7),
        'invited_by' => $owner->id,
    ]);

    expect($invitation->inviter->id)->toBe($owner->id);
});
