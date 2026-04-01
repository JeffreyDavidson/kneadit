<?php

use App\Events\Platform\PaymentFailed;
use App\Listeners\Platform\SendPaymentFailedEmailListener;
use App\Mail\Platform\PaymentFailedMail;
use App\Models\Staff\User;
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
