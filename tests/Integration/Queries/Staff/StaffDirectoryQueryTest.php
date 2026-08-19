<?php

use App\Enums\Staff\UserRole;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use App\Queries\Staff\StaffDirectoryQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('members orders owners before managers and staff', function () {
    User::factory()->create(['role' => UserRole::Staff]);
    User::factory()->create(['role' => UserRole::Owner]);
    User::factory()->create(['role' => UserRole::Manager]);

    $roles = StaffDirectoryQuery::members()->pluck('role')->all();

    expect($roles)->toBe([UserRole::Owner, UserRole::Manager, UserRole::Staff]);
});

test('pendingInvitations excludes expired and accepted invitations', function () {
    $pending = StaffInvitation::factory()->create();
    StaffInvitation::factory()->expired()->create();
    StaffInvitation::factory()->accepted()->create();

    $invitations = StaffDirectoryQuery::pendingInvitations();

    expect($invitations)->toHaveCount(1)
        ->and($invitations->sole()->is($pending))->toBeTrue();
});
