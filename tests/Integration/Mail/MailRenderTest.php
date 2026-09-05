<?php

use App\Mail\Platform\HealthAlertMail;
use App\Mail\Platform\NewSubscriberNotificationMail;
use App\Mail\Platform\PaymentFailedMail;
use App\Mail\Platform\ScheduledCheckinMail;
use App\Mail\Platform\TrialExpiredMail;
use App\Mail\Platform\TrialReminderMail;
use App\Mail\Platform\WelcomeBakerMail;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

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
    $mail = new ScheduledCheckinMail(
        body: 'Check-in body text',
        emailSubject: 'How is your bakery going?',
        adminUrl: 'https://test-bakery.kneadit.test/admin',
    );

    $mail->assertHasSubject('How is your bakery going?');
    expect($mail->render())
        ->toBeString()
        ->toContain('https://test-bakery.kneadit.test/admin');
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
    $mail = new WelcomeBakerMail('Jane', 'Jane\'s Bakery', 'https://example.test/admin', 'starter', '2026-04-26');

    $mail->assertHasSubject("Welcome to KneadIt — Jane's Bakery is ready!");
    expect($mail->render())->toBeString();
});

test('NewSubscriberNotification has correct subject and renders', function () {
    $mail = new NewSubscriberNotificationMail('Jane', 'jane@test.com', 'Jane\'s Bakery', 'janes-bakery', 'starter');

    $mail->assertHasSubject("New KneadIt Signup — Jane's Bakery");
    expect($mail->render())->toBeString();
});
