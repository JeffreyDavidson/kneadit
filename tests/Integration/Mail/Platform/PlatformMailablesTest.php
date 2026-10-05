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
use App\Models\Staff\User;
use Illuminate\Mail\Mailable;

beforeEach(function () {
    setUpCentralOnlyTest();

    config(['app.url' => 'http://kneadit.test', 'tenancy.tenant_domain' => 'kneadit.test']);
});

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
