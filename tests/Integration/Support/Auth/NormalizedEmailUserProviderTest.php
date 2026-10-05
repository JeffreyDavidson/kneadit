<?php

use App\Models\Staff\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('a user can sign in with any casing or padding of their email', function (string $submitted) {
    $user = User::factory()->create(['email' => 'foo@example.com', 'password' => Hash::make('password123')]);

    $attempted = auth()->guard('web')->attempt(['email' => $submitted, 'password' => 'password123']);

    expect($attempted)->toBeTrue()
        ->and(auth()->guard('web')->id())->toBe($user->id);
})->with([
    'exact' => 'foo@example.com',
    'upper case' => 'FOO@example.com',
    'padded' => ' foo@example.com ',
]);

test('a legacy mixed-case account can still sign in with the lowercased email', function (string $submitted) {
    $user = User::factory()->create(['email' => 'Foo@x.com', 'password' => Hash::make('password123')]);

    $attempted = auth()->guard('web')->attempt(['email' => $submitted, 'password' => 'password123']);

    expect($attempted)->toBeTrue()
        ->and(auth()->guard('web')->id())->toBe($user->id);
})->with([
    'lower case' => 'foo@x.com',
    'exact' => 'Foo@x.com',
    'upper case' => 'FOO@X.COM',
]);

test('the wrong password still fails whatever the casing of the email', function () {
    User::factory()->create(['email' => 'foo@example.com', 'password' => Hash::make('password123')]);

    expect(auth()->guard('web')->attempt(['email' => 'FOO@example.com', 'password' => 'wrong-password']))->toBeFalse();
});

test('an unknown email does not sign in', function () {
    User::factory()->create(['email' => 'foo@example.com', 'password' => Hash::make('password123')]);

    expect(auth()->guard('web')->attempt(['email' => 'bar@example.com', 'password' => 'password123']))->toBeFalse();
});

test('an exact match wins when two legacy accounts differ only by case', function () {
    User::factory()->create(['email' => 'Foo@x.com', 'password' => Hash::make('first-password1')]);
    $second = User::factory()->create(['email' => 'foo@x.com', 'password' => Hash::make('second-password1')]);

    $attempted = auth()->guard('web')->attempt(['email' => 'foo@x.com', 'password' => 'second-password1']);

    expect($attempted)->toBeTrue()
        ->and(auth()->guard('web')->id())->toBe($second->id);
});
