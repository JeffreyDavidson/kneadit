<?php

use App\Events\Platform\ScheduledCheckinDue;
use App\Listeners\Platform\SendScheduledCheckinEmailListener;
use App\Mail\Platform\ScheduledCheckinMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends scheduled checkin email to the tenant', function () {
    Mail::fake();

    $event = new ScheduledCheckinDue(
        tenantEmail: 'baker@example.com',
        body: 'How are things going?',
        subject: 'Week 1 Check-in',
        adminUrl: 'https://baker.kneadit.test/admin',
    );

    $listener = resolve(SendScheduledCheckinEmailListener::class);
    $listener->handle($event);

    Mail::assertQueued(
        ScheduledCheckinMail::class,
        fn (ScheduledCheckinMail $mail) => $mail->hasTo('baker@example.com')
            && $mail->adminUrl === 'https://baker.kneadit.test/admin',
    );
});

test('failed method logs a warning with email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'SendScheduledCheckinEmailListener failed'
            && $context['email'] === 'baker@example.com'
            && $context['error'] === 'SMTP timeout');

    $event = new ScheduledCheckinDue('baker@example.com', 'How are things going?', 'Week 1 Check-in');

    $listener = resolve(SendScheduledCheckinEmailListener::class);
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});

test('it supports queued events created with a tenant id', function () {
    Mail::fake();
    Config::set('app.url', 'http://kneadit.test:8000');

    $event = new ScheduledCheckinDue(
        tenantEmail: 'baker@example.com',
        body: 'How are things going?',
        subject: 'Week 1 Check-in',
        tenantId: 'legacy-baker',
    );

    resolve(SendScheduledCheckinEmailListener::class)->handle($event);

    Mail::assertQueued(
        ScheduledCheckinMail::class,
        fn (ScheduledCheckinMail $mail) => $mail->adminUrl === 'http://legacy-baker.kneadit.test:8000/admin',
    );
});
