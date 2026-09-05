<?php

use App\Events\Platform\TrialExpired;
use App\Listeners\Platform\SendTrialExpiredEmailListener;
use App\Mail\Platform\TrialExpiredMail;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends trial expired email to the user', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new TrialExpired(
        user: $user,
        tenantId: 'sweet-treats',
        adminUrl: 'https://sweet-treats.kneadit.test/admin',
    );

    $listener = resolve(SendTrialExpiredEmailListener::class);
    $listener->handle($event);

    Mail::assertQueued(
        TrialExpiredMail::class,
        fn (TrialExpiredMail $mail) => $mail->hasTo('baker@example.com')
            && $mail->adminUrl === 'https://sweet-treats.kneadit.test/admin',
    );
});

test('failed method logs a warning with email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'SendTrialExpiredEmailListener failed'
            && $context['email'] === 'baker@example.com'
            && $context['error'] === 'SMTP timeout');

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new TrialExpired($user, 'sweet-treats');

    $listener = resolve(SendTrialExpiredEmailListener::class);
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});

test('it supports queued events created with a tenant id', function () {
    Mail::fake();
    Config::set('app.url', 'http://kneadit.test:8000');

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new TrialExpired($user, 'legacy-bakery');

    resolve(SendTrialExpiredEmailListener::class)->handle($event);

    Mail::assertQueued(
        TrialExpiredMail::class,
        fn (TrialExpiredMail $mail) => $mail->adminUrl === 'http://legacy-bakery.kneadit.test:8000/admin',
    );
});
