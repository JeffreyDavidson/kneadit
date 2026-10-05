<?php

use App\Models\Staff\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\post;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('forgot password page renders', function () {
    $this->get(route('password.request'))->assertOk();
});

test('forgot password answers the same way for known and unknown emails', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'baker@example.com']);

    $known = post(route('password.email'), ['email' => 'baker@example.com']);
    $unknown = post(route('password.email'), ['email' => 'nobody@example.com']);

    expect($unknown->getStatusCode())->toBe($known->getStatusCode());
    $known->assertSessionHas('status', "If an account exists for that email, we've sent a reset link.")
        ->assertSessionHasNoErrors();
    $unknown->assertSessionHas('status', "If an account exists for that email, we've sent a reset link.")
        ->assertSessionHasNoErrors();
    Notification::assertSentTo($user, ResetPassword::class);
    Notification::assertSentTimes(ResetPassword::class, 1);
});

test('forgot password keeps its throttling message', function () {
    Notification::fake();
    User::factory()->create(['email' => 'baker@example.com']);

    post(route('password.email'), ['email' => 'baker@example.com']);
    $response = post(route('password.email'), ['email' => 'baker@example.com']);

    $response->assertSessionHasErrors('email');
});
