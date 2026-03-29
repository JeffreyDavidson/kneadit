<?php

use App\Actions\Staff\AcceptStaffInvitation;
use App\Enums\UserRole;
use App\Models\StaffInvitation;
use App\Models\User;

beforeEach(fn () => setUpTenantTest());

it('creates a new user and marks invitation as accepted', function () {
    $owner = User::query()->create([
        'name' => 'Owner',
        'email' => 'owner@test.com',
        'password' => bcrypt('password'),
    ]);

    $invitation = StaffInvitation::query()->create([
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
