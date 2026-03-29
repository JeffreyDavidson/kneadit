<?php

use App\Actions\Staff\SendStaffInvitation;
use App\Enums\UserRole;
use App\Mail\StaffInvitationMail;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => setUpTenantTest());

it('creates an invitation and sends the email', function () {
    Mail::fake();

    $inviter = User::query()->create([
        'name' => 'Owner',
        'email' => 'owner@test.com',
        'password' => bcrypt('password'),
    ]);

    $action = new SendStaffInvitation;
    $invitation = $action(
        email: 'new@test.com',
        role: UserRole::Staff,
        invitedBy: $inviter->id,
    );

    expect($invitation)->toBeInstanceOf(StaffInvitation::class)->and($invitation->email)->toBe('new@test.com')->and($invitation->role)->toBe(UserRole::Staff)->and($invitation->token)->toHaveLength(64);

    Mail::assertQueued(StaffInvitationMail::class);
});
