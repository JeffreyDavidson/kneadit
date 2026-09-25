<?php

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Database\Seeders\LocalPlatformAdminSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

beforeEach(function () {
    setUpCentralTest();

    config([
        'kneadit.seed_admin.name' => 'Jeffrey Davidson',
        'kneadit.seed_admin.email' => 'local-admin@example.test',
        'kneadit.seed_admin.password' => null,
    ]);
});

test('local platform admin seeding is refused outside local', function () {
    expect(fn () => resolve(LocalPlatformAdminSeeder::class)->run())
        ->toThrow(RuntimeException::class, 'may only run in the local environment')
        ->and(User::query()->where('role', UserRole::PlatformAdmin)->exists())->toBeFalse();
});

test('the seeder does nothing when email or password is not configured', function () {
    $environment = app('env');
    app()->instance('env', 'local');

    try {
        resolve(LocalPlatformAdminSeeder::class)->run();
    } finally {
        app()->instance('env', $environment);
    }

    expect(User::query()->where('role', UserRole::PlatformAdmin)->exists())->toBeFalse();
});

test('the seeder updates the sole existing local platform admin', function () {
    $password = Str::random(40);

    config(['kneadit.seed_admin.password' => $password]);

    $existingAdmin = User::factory()->platformAdmin()->create([
        'email' => 'previous-admin@kneadit.test',
    ]);

    $environment = app('env');
    app()->instance('env', 'local');

    try {
        resolve(LocalPlatformAdminSeeder::class)->run();
    } finally {
        app()->instance('env', $environment);
    }

    $existingAdmin->refresh();

    expect($existingAdmin->email)->toBe('local-admin@example.test')
        ->and($existingAdmin->name)->toBe('Jeffrey Davidson')
        ->and($existingAdmin->role)->toBe(UserRole::PlatformAdmin)
        ->and(Hash::check($password, $existingAdmin->password))->toBeTrue()
        ->and(User::query()->where('role', UserRole::PlatformAdmin)->count())->toBe(1);
});

test('the seeder requires an unambiguous account when multiple local admins exist', function () {
    config(['kneadit.seed_admin.password' => Str::random(40)]);

    User::factory()->platformAdmin()->count(2)->create();

    $environment = app('env');
    app()->instance('env', 'local');

    try {
        expect(fn () => resolve(LocalPlatformAdminSeeder::class)->run())
            ->toThrow(RuntimeException::class, 'Multiple local platform administrators exist');
    } finally {
        app()->instance('env', $environment);
    }

    expect(User::query()->where('role', UserRole::PlatformAdmin)->count())->toBe(2);
});
