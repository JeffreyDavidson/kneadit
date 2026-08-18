<?php

use App\Events\Platform\PaymentFailed;
use App\Listeners\Platform\SendPaymentFailedAlertListener;
use App\Mail\Platform\PaymentFailedAlertMail;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends payment-failed alert to platform admin with structured data', function () {
    Mail::fake();
    config(['mail.platform_notify' => 'admin@kneadit.com']);

    $user = User::factory()->create(['name' => 'Jane Baker', 'email' => 'jane@example.com']);
    $event = new PaymentFailed($user, null, 29.00);

    (new SendPaymentFailedAlertListener)->handle($event);

    Mail::assertQueued(
        PaymentFailedAlertMail::class,
        fn (PaymentFailedAlertMail $mail): bool => $mail->hasTo('admin@kneadit.com')
            && $mail->user->is($user)
            && $mail->amount === 29.00
            && $mail->tenant === null,
    );
});

test('failed method logs a warning with user email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendPaymentFailedAlertListener failed', Mockery::on(fn (array $context) => $context['user'] === 'jane@example.com'
            && $context['error'] === 'SMTP timeout'));

    $user = User::factory()->create(['email' => 'jane@example.com']);
    $event = new PaymentFailed($user, null, 29.00);

    $listener = new SendPaymentFailedAlertListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
