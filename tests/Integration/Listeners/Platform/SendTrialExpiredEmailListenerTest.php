<?php

use App\Events\Platform\TrialExpired;
use App\Listeners\Platform\SendTrialExpiredEmailListener;
use App\Mail\Platform\TrialExpiredMail;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends trial expired email to the user', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new TrialExpired($user, 'sweet-treats');

    $listener = new SendTrialExpiredEmailListener;
    $listener->handle($event);

    Mail::assertQueued(TrialExpiredMail::class, fn (TrialExpiredMail $mail) => $mail->hasTo('baker@example.com'));
});

test('failed method logs a warning with email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'SendTrialExpiredEmailListener failed'
            && $context['email'] === 'baker@example.com'
            && $context['error'] === 'SMTP timeout');

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new TrialExpired($user, 'sweet-treats');

    $listener = new SendTrialExpiredEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
