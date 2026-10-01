<?php

use App\Models\Staff\User;
use App\Services\Platform\HealthChecks\UsersTableCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it passes and reports the number of users', function () {
    User::factory()->count(2)->create();

    $result = (new UsersTableCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Users table OK (2 users)');
});

test('it passes for an empty users table', function () {
    $result = (new UsersTableCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Users table OK (0 users)');
});

test('it fails with the query error when the users table cannot be read', function () {
    config([
        'database.connections.without-users' => ['driver' => 'sqlite', 'database' => ':memory:'],
        'database.default' => 'without-users',
    ]);

    $result = (new UsersTableCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toStartWith('Users table query failed: ')
        ->toContain('no such table: users');
});
