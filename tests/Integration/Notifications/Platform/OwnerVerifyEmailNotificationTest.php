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

test('the verification email is branded KneadIt and renders without tenant settings', function () {
    $user = User::factory()->owner()->unverified()->create();

    $message = (new OwnerVerifyEmailNotification)->toMail($user);

    expect($message->subject)->toBe('Verify your KneadIt email')
        ->and(implode("\n", $message->introLines))->toContain('KneadIt')
        ->and($message->render()->toHtml())->toContain('Verify email');
});

test('the link is signed, expiring and uses the central app host', function () {
    $user = User::factory()->owner()->unverified()->create();

    $message = (new OwnerVerifyEmailNotification)->toMail($user);

    expect(parse_url($message->actionUrl, PHP_URL_HOST))->toBe('app.getkneadit.test')
        ->and($message->actionUrl)->toContain('signature=')
        ->and($message->actionUrl)->toContain('expires=');
});

test('the user sends this notification as their verification email', function () {
    Notification::fake();
    $user = User::factory()->owner()->unverified()->create();

    $user->sendEmailVerificationNotification();

    Notification::assertSentTo($user, OwnerVerifyEmailNotification::class);
});
