<?php

use App\Models\Staff\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('forgot password page loads', function () {
    $response = get('/forgot-password');

    $response->assertOk();
    $response->assertSee('Reset your password');
});

test('reset link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'baker@example.com',
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
    $user = User::factory()->create([
        'email' => 'baker@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::createToken($user);

    $response = post('/reset-password', [
        'token' => $token,
        'email' => 'baker@example.com',
        'password' => 'newPassword1',
        'password_confirmation' => 'newPassword1',
    ]);

    $response->assertRedirect('/login');
    expect(Hash::check('newPassword1', $user->fresh()->password))->toBeTrue();
});

test('reset password page loads', function () {
    $response = get('/reset-password/fake-token?email=baker@example.com');

    $response->assertOk();
    $response->assertSee('Set new password');
});

test('reset requires valid token', function () {
    User::factory()->create(['email' => 'baker@example.com']);

    $response = post('/reset-password', [
        'token' => 'invalid-token',
        'email' => 'baker@example.com',
        'password' => 'newPassword1',
        'password_confirmation' => 'newPassword1',
    ]);

    $response->assertSessionHasErrors('email');
});

test('reset requires password confirmation', function () {
    $response = post('/reset-password', [
        'token' => 'some-token',
        'email' => 'baker@example.com',
        'password' => 'newPassword1',
        'password_confirmation' => 'wrong-confirmation',
    ]);

    $response->assertSessionHasErrors('password');
});

test('reset link is sent when the email differs from a legacy mixed-case account only by case', function (string $submitted) {
    Notification::fake();
    $user = User::factory()->create(['email' => 'Baker@Example.com']);

    post('/forgot-password', ['email' => $submitted]);

    Notification::assertSentTo($user, ResetPassword::class);
})->with([
    'lower case' => 'baker@example.com',
    'padded' => ' baker@example.com ',
]);

test('password can be reset for a legacy mixed-case account using any casing of the email', function () {
    $user = User::factory()->create(['email' => 'Baker@Example.com', 'password' => Hash::make('old-password')]);
    $token = Password::createToken($user);

    $response = post('/reset-password', [
        'token' => $token,
        'email' => 'baker@example.com',
        'password' => 'newPassword1',
        'password_confirmation' => 'newPassword1',
    ]);

    $response->assertRedirect('/login');
    expect(Hash::check('newPassword1', $user->fresh()->password))->toBeTrue();
});

test('register page has forgot password link', function () {
    $response = get('/register');

    $response->assertOk();
    $response->assertSee('forgot-password');
});
