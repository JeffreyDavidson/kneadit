<?php

use App\Events\Platform\StaffInvitationSent;
use App\Listeners\Platform\SendStaffInvitationEmailListener;
use App\Mail\Platform\StaffInvitationMail;
use App\Models\Staff\StaffInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
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

test('failed method logs a warning with email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('Staff invitation email failed', Mockery::on(fn (array $context) => $context['email'] === 'newstaff@example.com'
            && $context['error'] === 'SMTP timeout'));

    $invitation = StaffInvitation::factory()->create(['email' => 'newstaff@example.com']);
    $event = new StaffInvitationSent($invitation, 'Sweet Treats Bakery', 'https://example.com/accept/abc123');

    $listener = new SendStaffInvitationEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
