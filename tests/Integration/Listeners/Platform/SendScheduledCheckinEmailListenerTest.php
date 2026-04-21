<?php

use App\Events\Platform\ScheduledCheckinDue;
use App\Listeners\Platform\SendScheduledCheckinEmailListener;
use App\Mail\Platform\ScheduledCheckinMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
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

test('failed method logs a warning with email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendScheduledCheckinEmailListener failed', Mockery::on(fn (array $context) => $context['email'] === 'baker@example.com'
            && $context['error'] === 'SMTP timeout'));

    $event = new ScheduledCheckinDue('baker@example.com', 'How are things going?', 'Week 1 Check-in');

    $listener = new SendScheduledCheckinEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
