<?php

use App\Events\Platform\TrialReminding;
use App\Listeners\Platform\SendTrialReminderEmailListener;
use App\Mail\Platform\TrialReminderMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends trial reminder email to the user', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new TrialReminding($user, 'Sweet Treats Bakery', 3);

    $listener = new SendTrialReminderEmailListener;
    $listener->handle($event);

    Mail::assertQueued(TrialReminderMail::class, fn (TrialReminderMail $mail) => $mail->hasTo('baker@example.com'));
});
