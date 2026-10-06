<?php

use App\Models\Staff\User;
use App\Notifications\Platform\OwnerVerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    setUpCentralOnlyTest();
    config(['app.url' => 'https://app.getkneadit.test']);
    URL::forceRootUrl('https://app.getkneadit.test');
});

test('the verification email uses the KneadIt layout and renders without tenant settings', function () {
    $user = User::factory()->owner()->unverified()->create(['name' => 'Jane Baker']);

    $message = (new OwnerVerifyEmailNotification)->toMail($user);
    $html = (string) $message->render();

    expect($message->subject)->toBe('Verify your KneadIt email')
        ->and($message->view)->toBe(['emails.platform.verify-email', 'emails.platform.verify-email-text'])
        ->and($html)->toContain('https://app.getkneadit.test/images/logo-transparent.png')
        ->and($html)->toContain('The bakery management platform for cottage food bakers')
        ->and($html)->toContain('Hi Jane Baker')
        ->and($html)->toContain('Verify email')
        ->and($html)->toContain(e($message->viewData['verificationUrl']));
});

test('the plain text version has the link', function () {
    $user = User::factory()->owner()->unverified()->create(['name' => 'Jane Baker']);

    $message = (new OwnerVerifyEmailNotification)->toMail($user);
    $text = view('emails.platform.verify-email-text', $message->viewData)->render();

    expect($text)->toContain('Hi Jane Baker')
        ->and($text)->toContain($message->viewData['verificationUrl']);
});

test('the link is signed, expiring and uses the central app host', function () {
    $user = User::factory()->owner()->unverified()->create();

    $url = (new OwnerVerifyEmailNotification)->toMail($user)->viewData['verificationUrl'];

    expect(parse_url($url, PHP_URL_HOST))->toBe('app.getkneadit.test')
        ->and($url)->toContain('signature=')
        ->and($url)->toContain('expires=');
});

test('the user sends this notification as their verification email', function () {
    Notification::fake();
    $user = User::factory()->owner()->unverified()->create();

    $user->sendEmailVerificationNotification();

    Notification::assertSentTo($user, OwnerVerifyEmailNotification::class);
});
