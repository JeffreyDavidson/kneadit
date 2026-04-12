<?php

use App\Enums\Staff\UserRole;
use App\Filament\Pages\Operations\StaffManagement;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
    test()->page = new StaffManagement;
});

test('invite email defaults to empty', function () {
    expect(test()->page->inviteEmail)->toBeEmpty();
});

test('invite role defaults to staff', function () {
    expect(test()->page->inviteRole)->toBe(UserRole::Staff->value);
});

test('get title returns team management', function () {
    expect(test()->page->getTitle())->toBe('Team Management');
});

test('get team members returns users sorted by role', function () {
    User::factory()->create(['role' => UserRole::Manager]);
    User::factory()->create(['role' => UserRole::Staff]);

    $members = test()->page->getTeamMembers();

    // Owner first (from beforeEach), then manager, then staff
    expect($members)->toHaveCount(3)
        ->and($members->first()->role)->toBe(UserRole::Owner);
});

test('get pending invitations returns only valid unexpired invitations', function () {
    StaffInvitation::factory()->create([
        'email' => 'valid@test.com',
        'accepted_at' => null,
        'expires_at' => now()->addDay(),
    ]);
    StaffInvitation::factory()->create([
        'email' => 'expired@test.com',
        'accepted_at' => null,
        'expires_at' => now()->subDay(),
    ]);
    StaffInvitation::factory()->create([
        'email' => 'accepted@test.com',
        'accepted_at' => now(),
        'expires_at' => now()->addDay(),
    ]);

    $invitations = test()->page->getPendingInvitations();

    expect($invitations)->toHaveCount(1)
        ->and($invitations->first()->email)->toBe('valid@test.com');
});

test('send invitation validates required email', function () {
    test()->page->inviteEmail = '';

    expect(fn () => test()->page->sendInvitation())
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

test('send invitation validates email format', function () {
    test()->page->inviteEmail = 'not-an-email';

    expect(fn () => test()->page->sendInvitation())
        ->toThrow(Illuminate\Validation\ValidationException::class);
});

test('change role with invalid role does nothing', function () {
    $user = User::factory()->create(['role' => UserRole::Staff]);

    test()->page->changeRole($user->id, 'invalid_role');

    $user->refresh();
    expect($user->role)->toBe(UserRole::Staff);
});
