<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    setUpCentralTest();
});

test('forgot password page loads', function () {
    $response = get('/forgot-password');

    $response->assertOk();
    $response->assertSee('Reset your password');
});

test('reset link can be requested', function () {
    Notification::fake();

    $user = User::create([
        'name' => 'Test Baker',
        'email' => 'baker@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = post('/forgot-password', ['email' => 'baker@example.com']);

    $response->assertSessionHas('status');
    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset link not sent for invalid email', function () {
    Notification::fake();

    post('/forgot-password', ['email' => 'nobody@example.com']);

    Notification::assertNothingSent();
});

test('reset link requires email', function () {
    $response = post('/forgot-password', []);

    $response->assertSessionHasErrors('email');
});

test('password can be reset', function () {
    $user = User::create([
        'name' => 'Test Baker',
        'email' => 'baker@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::createToken($user);

    $response = post('/reset-password', [
        'token' => $token,
        'email' => 'baker@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect('/login');
    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('reset password page loads', function () {
    $response = get('/reset-password/fake-token?email=baker@example.com');

    $response->assertOk();
    $response->assertSee('Set new password');
});

test('reset requires valid token', function () {
    User::create([
        'name' => 'Test Baker',
        'email' => 'baker@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = post('/reset-password', [
        'token' => 'invalid-token',
        'email' => 'baker@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
});

test('reset requires password confirmation', function () {
    $response = post('/reset-password', [
        'token' => 'some-token',
        'email' => 'baker@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'wrong-confirmation',
    ]);

    $response->assertSessionHasErrors('password');
});

test('register page has forgot password link', function () {
    $response = get('/register');

    $response->assertOk();
    $response->assertSee('forgot-password');
});
