<?php

use App\Actions\Staff\SendStaffInvitation;
use App\Enums\Staff\UserRole;
use App\Mail\Platform\StaffInvitationMail;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpTenantTest());

it('creates an invitation and sends the email', function () {
    Mail::fake();

    $inviter = User::factory()->owner()->create([
        'name' => 'Owner',
        'email' => 'owner@test.com',
        'password' => bcrypt('password'),
    ]);

    $action = resolve(SendStaffInvitation::class);
    $invitation = $action(
        email: 'new@test.com',
        role: UserRole::Staff,
        invitedBy: $inviter->id,
    );

    expect($invitation)->toBeInstanceOf(StaffInvitation::class)->and($invitation->email)->toBe('new@test.com')->and($invitation->role)->toBe(UserRole::Staff)->and($invitation->token)->toHaveLength(64);

    Mail::assertQueued(StaffInvitationMail::class);
});

it('throws when user is already a team member', function () {
    User::factory()->create(['email' => 'existing@test.com']);

    $inviter = User::factory()->owner()->create();

    $action = resolve(SendStaffInvitation::class);
    $action(
        email: 'existing@test.com',
        role: UserRole::Staff,
        invitedBy: $inviter->id,
    );
})->throws(App\Exceptions\Staff\StaffInvitationException::class, 'already a team member');

it('throws when a pending invitation already exists', function () {
    $inviter = User::factory()->owner()->create();

    StaffInvitation::factory()->create([
        'email' => 'pending@test.com',
        'expires_at' => now()->addDays(7),
        'accepted_at' => null,
        'invited_by' => $inviter->id,
    ]);

    $action = resolve(SendStaffInvitation::class);
    $action(
        email: 'pending@test.com',
        role: UserRole::Staff,
        invitedBy: $inviter->id,
    );
})->throws(App\Exceptions\Staff\StaffInvitationException::class, 'already pending');
