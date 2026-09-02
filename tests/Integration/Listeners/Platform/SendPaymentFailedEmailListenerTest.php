<?php

use App\Events\Platform\PaymentFailed;
use App\Listeners\Platform\SendPaymentFailedEmailListener;
use App\Mail\Platform\PaymentFailedMail;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends payment failed email to the user', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new PaymentFailed($user, null, 29.00);

    $listener = new SendPaymentFailedEmailListener;
    $listener->handle($event);

    Mail::assertQueued(PaymentFailedMail::class, fn (PaymentFailedMail $mail) => $mail->hasTo('baker@example.com'));
});

test('failed method logs a warning with user email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'SendPaymentFailedEmailListener failed'
            && $context['user'] === 'baker@example.com'
            && $context['error'] === 'SMTP timeout');

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new PaymentFailed($user, null, 29.00);

    $listener = new SendPaymentFailedEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
