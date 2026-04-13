<?php

use App\Events\Platform\TrialReminding;
use App\Listeners\Platform\SendTrialReminderEmailListener;
use App\Mail\Platform\TrialReminderMail;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
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

test('failed method logs a warning with email and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendTrialReminderEmailListener failed', Mockery::on(fn (array $context) => $context['email'] === 'baker@example.com'
            && $context['error'] === 'SMTP timeout'));

    $user = User::factory()->create(['email' => 'baker@example.com']);
    $event = new TrialReminding($user, 'Sweet Treats Bakery', 3);

    $listener = new SendTrialReminderEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
