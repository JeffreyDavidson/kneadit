<?php

use App\Models\Staff\User;
use App\Support\PlatformMailPreviews;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralOnlyTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    app()->detectEnvironment(fn (): string => 'local');
});

dataset('previewable emails', fn (): array => array_keys(PlatformMailPreviews::titles()));

test('the index lists every previewable email', function () {
    $response = get(route('mailPreviews.index'));

    $response->assertOk();
    foreach (PlatformMailPreviews::titles() as $slug => $title) {
        $response->assertSee($title)->assertSee(route('mailPreviews.show', ['mail' => $slug]));
    }
});

test('each email renders in the browser', function (string $slug) {
    $response = get(route('mailPreviews.show', ['mail' => $slug]));

    $response->assertOk();
    expect($response->getContent())->not->toBeEmpty();
})->with('previewable emails');

test('each email renders its plain-text version', function (string $slug) {
    $response = get(route('mailPreviews.show', ['mail' => $slug, 'format' => 'text']));

    $response->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->assertDontSeeHtml('<html');
})->with('previewable emails');

test('an email with no HTML version is shown as plain text', function () {
    $response = get(route('mailPreviews.show', ['mail' => 'trial-reminder']));

    $response->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
});

test('previewing sends no mail and saves no rows', function (string $slug) {
    Mail::fake();
    $users = User::count();

    get(route('mailPreviews.show', ['mail' => $slug]));

    Mail::assertNothingSent();
    Mail::assertNothingQueued();
    expect(User::count())->toBe($users);
})->with('previewable emails');

test('an unknown email is not found', function () {
    get(route('mailPreviews.show', ['mail' => 'not-an-email']))->assertNotFound();
});

test('the previews do not exist outside the local environment', function (string $environment) {
    app()->detectEnvironment(fn (): string => $environment);

    get(route('mailPreviews.index'))->assertNotFound();
    get(route('mailPreviews.show', ['mail' => 'welcome-baker']))->assertNotFound();
})->with(['production', 'staging', 'testing']);

test('the previews are not served on a bakery host', function () {
    get('http://janes-bakery.kneadit.test/mail-previews')->assertNotFound();
});
