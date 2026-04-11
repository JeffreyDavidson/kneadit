<?php

use App\Actions\Staff\AcceptStaffInvitation;
use App\Enums\Staff\UserRole;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;

beforeEach(fn () => setUpTenantTest());

it('creates a new user and marks invitation as accepted', function () {
    $owner = User::factory()->owner()->create([
        'name' => 'Owner',
        'email' => 'owner@test.com',
        'password' => bcrypt('password'),
    ]);

    $invitation = StaffInvitation::factory()->create([
        'email' => 'new@test.com',
        'role' => UserRole::Staff->value,
        'token' => 'test-token-123',
        'expires_at' => now()->addDays(7),
        'invited_by' => $owner->id,
    ]);

    $action = new AcceptStaffInvitation;
    $user = $action(
        invitation: $invitation,
        name: 'New Staff',
        password: 'password123',
    );

    expect($user)->toBeInstanceOf(User::class)->and($user->email)->toBe('new@test.com')->and($user->role)->toBe(UserRole::Staff)->and($invitation->refresh()->accepted_at)->not->toBeNull();
});

it('updates role when user already exists', function () {
    $existingUser = User::factory()->staff()->create([
        'email' => 'existing@test.com',
    ]);

    $invitation = StaffInvitation::factory()->create([
        'email' => 'existing@test.com',
        'role' => UserRole::Manager->value,
        'token' => 'test-token-456',
        'expires_at' => now()->addDays(7),
    ]);

    $action = new AcceptStaffInvitation;
    $user = $action(invitation: $invitation);

    expect($user->id)->toBe($existingUser->id)
        ->and($user->refresh()->role)->toBe(UserRole::Manager)
        ->and($invitation->refresh()->accepted_at)->not->toBeNull();
});
