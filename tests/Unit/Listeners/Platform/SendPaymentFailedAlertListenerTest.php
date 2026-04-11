<?php

use App\Events\Platform\PaymentFailed;
use App\Listeners\Platform\SendPaymentFailedAlertListener;
use App\Mail\Platform\HealthAlertMail;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends health alert to platform admin', function () {
    Mail::fake();
    config(['mail.platform_notify' => 'admin@kneadit.com']);

    $user = User::factory()->create(['name' => 'Jane Baker', 'email' => 'jane@example.com']);
    $event = new PaymentFailed($user, null, 29.00);

    $listener = new SendPaymentFailedAlertListener;
    $listener->handle($event);

    Mail::assertQueued(HealthAlertMail::class, fn (HealthAlertMail $mail) => $mail->hasTo('admin@kneadit.com'));
});

test('failed method logs a warning with user email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('Payment failed platform alert could not be sent', Mockery::on(fn (array $context) => $context['user'] === 'jane@example.com'
            && $context['error'] === 'SMTP timeout'));

    $user = User::factory()->create(['email' => 'jane@example.com']);
    $event = new PaymentFailed($user, null, 29.00);

    $listener = new SendPaymentFailedAlertListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
