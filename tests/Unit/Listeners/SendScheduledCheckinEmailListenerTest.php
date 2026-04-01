<?php

use App\Events\Platform\ScheduledCheckinDue;
use App\Listeners\Platform\SendScheduledCheckinEmailListener;
use App\Mail\Platform\ScheduledCheckinMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends scheduled checkin email to the tenant', function () {
    Mail::fake();

    $event = new ScheduledCheckinDue('baker@example.com', 'How are things going?', 'Week 1 Check-in');

    $listener = new SendScheduledCheckinEmailListener;
    $listener->handle($event);

    Mail::assertQueued(ScheduledCheckinMail::class, fn (ScheduledCheckinMail $mail) => $mail->hasTo('baker@example.com'));
});
