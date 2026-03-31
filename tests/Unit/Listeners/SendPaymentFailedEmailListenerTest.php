<?php

use App\Events\PaymentFailed;
use App\Listeners\SendPaymentFailedEmailListener;
use App\Mail\PaymentFailedMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends payment failed email to the user', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new PaymentFailed($user, null, 29.00);

    $listener = new SendPaymentFailedEmailListener;
    $listener->handle($event);

    Mail::assertQueued(PaymentFailedMail::class, fn (PaymentFailedMail $mail) => $mail->hasTo('baker@example.com'));
});
