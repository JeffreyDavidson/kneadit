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
