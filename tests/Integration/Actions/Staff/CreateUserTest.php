<?php

use App\Actions\Staff\CreateUser;
use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('creates a user with given data', function () {
    Event::fake([Registered::class]);

    $user = resolve(CreateUser::class)([
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'password' => 'secret123',
    ]);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->name->toBe('Jane Baker')
        ->email->toBe('jane@example.com');

    test()->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});

test('dispatches Registered event', function () {
    Event::fake([Registered::class]);

    resolve(CreateUser::class)([
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'password' => 'secret123',
    ]);

    Event::assertDispatched(Registered::class);
});

test('stores the email lowercased and trimmed', function () {
    Event::fake([Registered::class]);

    $user = resolve(CreateUser::class)([
        'name' => 'Jane Baker',
        'email' => ' Jane@Example.com ',
        'password' => 'secret123',
    ]);

    expect($user->email)->toBe('jane@example.com');
    test()->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});

test('creates the platform account as an Owner whatever the column default is', function () {
    Event::fake([Registered::class]);

    $user = resolve(CreateUser::class)([
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'password' => 'secret123',
    ]);

    expect($user->fresh()->role)->toBe(UserRole::Owner);
});
