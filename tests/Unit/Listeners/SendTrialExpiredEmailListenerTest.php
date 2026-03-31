<?php

use App\Events\TrialExpired;
use App\Listeners\SendTrialExpiredEmailListener;
use App\Mail\TrialExpiredMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends trial expired email to the user', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new TrialExpired($user, 'sweet-treats');

    $listener = new SendTrialExpiredEmailListener;
    $listener->handle($event);

    Mail::assertQueued(TrialExpiredMail::class, fn (TrialExpiredMail $mail) => $mail->hasTo('baker@example.com'));
});
