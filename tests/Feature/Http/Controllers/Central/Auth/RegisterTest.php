<?php

use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('register page renders for guests', function () {
    $this->get(route('register'))->assertOk();
});

test('user can register with valid data', function () {
    $this->post(route('register'), [
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'bakery_name' => 'Jane\'s Bakery',
        'terms' => true,
    ])->assertRedirect();

    test()->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    test()->assertAuthenticated();
});

test('register validates required fields', function () {
    $this->post(route('register'), [])
        ->assertSessionHasErrors(['name', 'email', 'password', 'bakery_name', 'terms']);
});

test('register rejects an email that differs from an existing one only by case or padding', function (string $submitted) {
    User::factory()->create(['email' => 'foo@example.com']);

    $this->post(route('register'), [
        'name' => 'Jane Baker',
        'email' => $submitted,
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'bakery_name' => 'Jane\'s Bakery',
        'terms' => true,
    ])->assertSessionHasErrors('email');

    expect(User::query()->count())->toBe(1);
})->with([
    'mixed case' => 'Foo@Example.com',
    'upper case' => 'FOO@EXAMPLE.COM',
    'padded' => ' foo@example.com ',
]);

test('register rejects an email that differs only by case from a legacy mixed-case account', function () {
    User::factory()->create(['email' => 'Foo@Example.com']);

    $this->post(route('register'), [
        'name' => 'Jane Baker',
        'email' => 'foo@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'bakery_name' => 'Jane\'s Bakery',
        'terms' => true,
    ])->assertSessionHasErrors('email');
});

test('register stores the email lowercased and trimmed', function () {
    $this->post(route('register'), [
        'name' => 'Jane Baker',
        'email' => ' Jane@Example.com ',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'bakery_name' => 'Jane\'s Bakery',
        'terms' => true,
    ])->assertRedirect();

    test()->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});
