<?php

use App\Mail\Platform\ContactFormMail;
use App\Mail\Platform\HealthAlertMail;
use App\Mail\Platform\NewSubscriberNotificationMail;
use App\Mail\Platform\PaymentFailedAlertMail;
use App\Mail\Platform\PaymentFailedMail;
use App\Mail\Platform\PlatformCampaignMail;
use App\Mail\Platform\ScheduledCheckinMail;
use App\Mail\Platform\TrialExpiredMail;
use App\Mail\Platform\TrialReminderMail;
use App\Mail\Platform\UnapprovedFreeForeverAlertMail;
use App\Mail\Platform\WelcomeBakerMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Mail\Mailable;

beforeEach(function () {
    setUpCentralOnlyTest();

    config(['app.url' => 'http://kneadit.test', 'tenancy.tenant_domain' => 'kneadit.test']);
});

function ownerWithBakery(): User
{
    $owner = User::factory()->owner()->create(['name' => 'Jane Baker']);

    Tenant::factory()->create(['user_id' => $owner->id]);

    return $owner;
}

dataset('platform mailables', [
    'contact form' => [fn (): Mailable => new ContactFormMail('Jane', 'jane@example.com', 'Hello there')],
    'health alert' => [fn (): Mailable => new HealthAlertMail('Database connection failed')],
    'new subscriber' => [fn (): Mailable => new NewSubscriberNotificationMail('Jane', 'jane@example.com', "Jane's Bakery", 'janes.kneadit.test', 'starter', 'http://kneadit.test/admin')],
    'payment failed alert' => [fn (): Mailable => new PaymentFailedAlertMail(User::factory()->owner()->create(), null, 29.0)],
    'payment failed' => [fn (): Mailable => new PaymentFailedMail(User::factory()->owner()->create())],
    'campaign' => [fn (): Mailable => new PlatformCampaignMail('Big news', 'Something happened.')],
    'scheduled check-in' => [fn (): Mailable => new ScheduledCheckinMail('Body', 'Subject', 'http://a.kneadit.test/admin', 'http://a.kneadit.test/help')],
    'trial expired' => [fn (): Mailable => new TrialExpiredMail(User::factory()->owner()->create(), 'http://a.kneadit.test/admin')],
    'trial reminder' => [fn (): Mailable => new TrialReminderMail(User::factory()->owner()->create(), "Jane's Bakery", 3)],
    'unapproved free forever' => [fn (): Mailable => new UnapprovedFreeForeverAlertMail([['id' => 'a', 'name' => 'A', 'email' => 'a@example.com']])],
    'welcome baker' => [fn (): Mailable => new WelcomeBakerMail('Jane', "Jane's Bakery", 'http://a.kneadit.test/admin', 'starter', '2026-10-19')],
]);

test('platform mailables render outside tenancy without any bakery settings', function (Closure $makeMail) {
    $mail = $makeMail();

    $html = $mail->render();

    expect($html)->toBeString()->not->toBeEmpty();
})->with('platform mailables');

test('a platform mail shows the bakery name it was given, not a settings value', function () {
    $mail = new WelcomeBakerMail('Jane', "Jane's Bakery", 'http://a.kneadit.test/admin', 'starter', '2026-10-19');

    expect($mail->render())->toContain('Jane&#039;s Bakery');
});

/**
 * Each case builds the mail with realistic data, plus the content its HTML and
 * its text version must both still carry.
 */
dataset('platform mail content', [
    'contact form' => [fn (): array => [
        new ContactFormMail('Jane', 'jane@example.com', 'Hello there'),
        ['Jane', 'jane@example.com', 'Hello there'],
        [],
    ]],
    'health alert' => [fn (): array => [
        new HealthAlertMail('Database connection failed'),
        ['Database connection failed'],
        ['Database connection failed'],
    ]],
    'new subscriber' => [fn (): array => [
        new NewSubscriberNotificationMail('Jane', 'jane@example.com', 'Jane Bakery', 'janes.kneadit.test', 'starter', 'http://kneadit.test/admin'),
        ['Jane', 'jane@example.com', 'Jane Bakery', 'janes.kneadit.test', 'Starter', 'http://kneadit.test/admin'],
        [],
    ]],
    'payment failed alert' => [function (): array {
        $user = ownerWithBakery();

        return [
            new PaymentFailedAlertMail($user, null, 29.0),
            ['Jane Baker', $user->email, '$29.00'],
            ['Jane Baker', $user->email, '$29.00'],
        ];
    }],
    'payment failed' => [function (): array {
        $user = ownerWithBakery();
        $billingUrl = resolve(TenantUrlGenerator::class)->billingForOwner($user);

        return [
            new PaymentFailedMail($user),
            ['Jane Baker', 'update your payment method', $billingUrl],
            ['Jane Baker', $billingUrl],
        ];
    }],
    'campaign' => [fn (): array => [
        new PlatformCampaignMail('Big news', 'Something happened.'),
        ['Something happened.'],
        [],
    ]],
    'scheduled check-in' => [fn (): array => [
        new ScheduledCheckinMail('Body text', 'Subject', 'Jane', 'http://a.kneadit.test/admin', 'http://a.kneadit.test/help'),
        ['Jane', 'Body text', 'http://a.kneadit.test/admin', 'http://a.kneadit.test/help'],
        ['Body text'],
    ]],
    'trial expired' => [function (): array {
        $user = ownerWithBakery();
        $billingUrl = resolve(TenantUrlGenerator::class)->billingForOwner($user);

        return [
            new TrialExpiredMail($user, 'http://a.kneadit.test/admin'),
            ['Jane Baker', 'free trial has expired', 'http://a.kneadit.test/admin'],
            ['Jane Baker', $billingUrl, 'http://a.kneadit.test/admin'],
        ];
    }],
    'trial reminder' => [function (): array {
        $user = ownerWithBakery();
        $billingUrl = resolve(TenantUrlGenerator::class)->billingForOwner($user);

        return [
            new TrialReminderMail($user, 'Jane Bakery', 3),
            ['Jane Baker', 'Jane Bakery', 'in 3 days', 'storefront will be paused', $billingUrl],
            ['Jane Baker', 'Jane Bakery', 'in 3 days', $billingUrl],
        ];
    }],
    'unapproved free forever' => [fn (): array => [
        new UnapprovedFreeForeverAlertMail([['id' => 'tenant-1', 'name' => 'Acme Bread', 'email' => 'a@example.com']]),
        ['tenant-1', 'Acme Bread', 'a@example.com'],
        [],
    ]],
    'welcome baker' => [fn (): array => [
        new WelcomeBakerMail('Jane', 'Jane Bakery', 'http://a.kneadit.test/admin', 'starter', '2026-10-19'),
        ['Jane', 'Jane Bakery', 'Starter', '2026-10-19', 'http://a.kneadit.test/admin'],
        [],
    ]],
]);

test('a platform mail renders inside the shared KneadIt layout', function (Closure $make) {
    [$mail] = $make();

    $mail->assertSeeInHtml('http://kneadit.test/images/logo-transparent.png', false);
    $mail->assertSeeInHtml('The bakery management platform for cottage food bakers');
})->with('platform mail content');

test('a platform mail still shows its key content in the HTML', function (Closure $make) {
    [$mail, $expected] = $make();

    foreach ($expected as $content) {
        $mail->assertSeeInHtml($content);
    }
})->with('platform mail content');

test('a platform mail with a text version still renders it', function (Closure $make) {
    [$mail, , $expectedText] = $make();

    expect($mail->content()->text !== null)->toBe($expectedText !== []);

    foreach ($expectedText as $content) {
        $mail->assertSeeInText($content);
    }
})->with('platform mail content');

test('the text-only platform mails now also have an HTML version', function (Closure $makeMail) {
    $content = $makeMail()->content();

    expect($content->view)->toBeString()->not->toBeEmpty()
        ->and($content->text)->toBeString()->not->toBeEmpty();
})->with([
    'health alert' => [fn (): Mailable => new HealthAlertMail('Database connection failed')],
    'payment failed alert' => [fn (): Mailable => new PaymentFailedAlertMail(User::factory()->owner()->create(), null, 29.0)],
    'payment failed' => [fn (): Mailable => new PaymentFailedMail(User::factory()->owner()->create())],
    'trial expired' => [fn (): Mailable => new TrialExpiredMail(User::factory()->owner()->create(), 'http://a.kneadit.test/admin')],
    'trial reminder' => [fn (): Mailable => new TrialReminderMail(User::factory()->owner()->create(), 'Jane Bakery', 3)],
]);
