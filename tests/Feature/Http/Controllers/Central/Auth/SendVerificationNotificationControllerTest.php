<?php

use App\Models\Staff\User;
use App\Notifications\Platform\OwnerVerifyEmailNotification;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('sends verification notification', function () {
    Notification::fake();

    $user = User::factory()->owner()->unverified()->create();

    actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect()
        ->assertSessionHas('message', 'Verification link sent!');

    Notification::assertSentTo($user, OwnerVerifyEmailNotification::class);
});

test('redirects guests to login (route auth middleware)', function () {
    Notification::fake();

    test()->post(route('verification.send'))
        ->assertRedirect(route('login'));

    Notification::assertNothingSent();
});
