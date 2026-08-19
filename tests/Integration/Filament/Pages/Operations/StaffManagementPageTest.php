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

test('get title returns team management', function () {
    expect(testFixture('page', StaffManagement::class)->getTitle())->toBe('Team Management');
});

test('get team members returns users sorted by role', function () {
    User::factory()->manager()->create();
    User::factory()->staff()->create();

    $members = testFixture('page', StaffManagement::class)->getTeamMembers();

    expect($members)->toHaveCount(3)
        ->and($members->firstOrFail()->role)->toBe(UserRole::Owner);
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

    $invitations = testFixture('page', StaffManagement::class)->getPendingInvitations();

    expect($invitations)->toHaveCount(1)
        ->and($invitations->firstOrFail()->email)->toBe('valid@test.com');
});
