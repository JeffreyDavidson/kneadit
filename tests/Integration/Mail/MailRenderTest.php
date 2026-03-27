<?php

use App\Mail\HealthAlertMail;
use App\Mail\NewSubscriberNotification;
use App\Mail\PaymentFailedMail;
use App\Mail\ScheduledCheckinMail;
use App\Mail\TrialExpiredMail;
use App\Mail\TrialReminderMail;
use App\Mail\WelcomeBaker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('HealthAlertMail has correct subject and renders', function () {
    $mail = new HealthAlertMail('Test alert');

    $mail->assertHasSubject('⚠️ KneadIt Health Check Alert');
    expect($mail->render())->toBeString();
});

test('PaymentFailedMail has correct subject and renders', function () {
    $user = User::factory()->owner()->create();
    $mail = new PaymentFailedMail($user);

    $mail->assertHasSubject('⚠️ Payment failed — action needed');
    expect($mail->render())->toBeString();
});

test('ScheduledCheckinMail has correct subject and renders', function () {
    $mail = new ScheduledCheckinMail('Check-in body text', 'How is your bakery going?');

    $mail->assertHasSubject('How is your bakery going?');
    expect($mail->render())->toBeString();
});

test('TrialExpiredMail has correct subject and renders', function () {
    $user = User::factory()->owner()->create();
    $mail = new TrialExpiredMail($user, 'test-tenant');

    $mail->assertHasSubject('Your KneadIt trial has expired');
    expect($mail->render())->toBeString();
});

test('TrialReminderMail has correct subject for 7 days and renders', function () {
    $user = User::factory()->owner()->create();
    $mail = new TrialReminderMail($user, 'Test Bakery', 7);

    expect($mail->render())->toBeString();
});

test('WelcomeBaker has correct subject and renders', function () {
    $mail = new WelcomeBaker('Jane', 'Jane\'s Bakery', 'https://example.test/admin', 'starter', '2026-04-26');

    $mail->assertHasSubject("Welcome to KneadIt — Jane's Bakery is ready!");
    expect($mail->render())->toBeString();
});

test('NewSubscriberNotification has correct subject and renders', function () {
    $mail = new NewSubscriberNotification('Jane', 'jane@test.com', 'Jane\'s Bakery', 'janes-bakery', 'starter');

    $mail->assertHasSubject("New KneadIt Signup — Jane's Bakery");
    expect($mail->render())->toBeString();
});
