<?php

use App\Events\Platform\StaffInvitationSent;
use App\Listeners\Platform\SendStaffInvitationEmailListener;
use App\Mail\StaffInvitationMail;
use App\Models\StaffInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends staff invitation email to the invitee', function () {
    Mail::fake();

    $invitation = StaffInvitation::factory()->create(['email' => 'newstaff@example.com']);
    $event = new StaffInvitationSent($invitation, 'Sweet Treats Bakery', 'https://example.com/accept/abc123');

    $listener = new SendStaffInvitationEmailListener;
    $listener->handle($event);

    Mail::assertQueued(StaffInvitationMail::class, fn (StaffInvitationMail $mail) => $mail->hasTo('newstaff@example.com'));
});
