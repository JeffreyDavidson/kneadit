<?php

use App\Mail\Platform\HealthAlertMail;
use App\Mail\Platform\NewSubscriberNotificationMail;
use App\Mail\Platform\PaymentFailedMail;
use App\Mail\Platform\ScheduledCheckinMail;
use App\Mail\Platform\TrialExpiredMail;
use App\Mail\Platform\TrialReminderMail;
use App\Mail\Platform\WelcomeBakerMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();

    config(['app.url' => 'http://kneadit.test', 'tenancy.tenant_domain' => 'kneadit.test']);
});

test('HealthAlertMail has correct subject and renders', function () {
    $mail = new HealthAlertMail('Test alert');

    $mail->assertHasSubject('⚠️ KneadIt Health Check Alert');
    expect($mail->render())->toBeString();
});

test('PaymentFailedMail has correct subject and links to the bakery admin billing page', function () {
    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['id' => 'sunrise', 'user_id' => $user->id]);
    $mail = new PaymentFailedMail($user);

    $mail->assertHasSubject('⚠️ Payment failed — action needed');
    expect($mail->render())
        ->toBeString()
        ->toContain('http://sunrise.kneadit.test/admin/upgrade-plan')
        ->not->toContain('/billing/portal');
});

test('ScheduledCheckinMail has correct subject and renders', function () {
    $mail = new ScheduledCheckinMail(
        body: 'Check-in body text',
        emailSubject: 'How is your bakery going?',
        adminUrl: 'https://test-bakery.kneadit.test/admin',
        helpUrl: 'https://test-bakery.kneadit.test/admin/help-center',
    );

    $mail->assertHasSubject('How is your bakery going?');
    expect($mail->render())
        ->toBeString()
        ->toContain('https://test-bakery.kneadit.test/admin')
        ->toContain('https://test-bakery.kneadit.test/admin/help-center');
});

test('TrialExpiredMail has correct subject and links to the bakery admin billing page', function () {
    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['id' => 'sunrise', 'user_id' => $user->id]);
    $mail = new TrialExpiredMail($user, 'https://test-tenant.kneadit.test/admin');

    $mail->assertHasSubject('Your KneadIt trial has expired');
    expect($mail->render())
        ->toBeString()
        ->toContain('http://sunrise.kneadit.test/admin/upgrade-plan')
        ->toContain('https://test-tenant.kneadit.test/admin')
        ->not->toContain('/billing/plans');
});

test('TrialReminderMail has correct subject for 7 days and links to the bakery admin billing page', function () {
    $user = User::factory()->owner()->create();
    Tenant::factory()->create(['id' => 'sunrise', 'user_id' => $user->id]);
    $mail = new TrialReminderMail($user, 'Test Bakery', 7);

    expect($mail->render())
        ->toBeString()
        ->toContain('http://sunrise.kneadit.test/admin/upgrade-plan')
        ->not->toContain('/billing/plans');
});

test('billing mails for an owner with no bakery omit the billing link instead of failing', function () {
    $user = User::factory()->owner()->create();

    expect(new TrialReminderMail($user, 'Test Bakery', 7)->render())->not->toContain('upgrade-plan')
        ->and(new PaymentFailedMail($user)->render())->not->toContain('upgrade-plan');
});

test('WelcomeBaker has correct subject and renders', function () {
    $mail = new WelcomeBakerMail('Jane', 'Jane\'s Bakery', 'https://example.test/admin', 'starter', '2026-04-26');

    $mail->assertHasSubject("Welcome to KneadIt — Jane's Bakery is ready!");
    expect($mail->render())->toBeString();
});

test('NewSubscriberNotification has correct subject and renders', function () {
    $mail = new NewSubscriberNotificationMail(
        'Jane',
        'jane@test.com',
        'Jane\'s Bakery',
        'janes-bakery.kneadit.test',
        'starter',
        'https://kneadit.test/central',
    );

    $mail->assertHasSubject("New KneadIt Signup — Jane's Bakery");
    expect($mail->render())
        ->toBeString()
        ->toContain('janes-bakery.kneadit.test')
        ->toContain('https://kneadit.test/central');
});
