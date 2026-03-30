<?php

use App\Actions\Tenants\SendOnboardingEmails;
use App\Mail\NewSubscriberNotificationMail;
use App\Mail\WelcomeBakerMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('sends welcome and notification emails', function () {
    Mail::fake();

    $user = User::factory()->owner()->create();

    resolve(SendOnboardingEmails::class)($user, 'Sweet Bakery', 'sweet-bakery', 'https://example.com/admin');

    Mail::assertQueued(WelcomeBakerMail::class, fn ($mail) => $mail->hasTo($user->email));
    Mail::assertQueued(NewSubscriberNotificationMail::class);
});
