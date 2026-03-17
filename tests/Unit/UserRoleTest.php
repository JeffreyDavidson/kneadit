<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

test('user model has role methods', function () {
    $user = new User;

    expect(method_exists($user, 'isOwner'))->toBeTrue();
    expect(method_exists($user, 'isManager'))->toBeTrue();
    expect(method_exists($user, 'isStaff'))->toBeTrue();
    expect(method_exists($user, 'hasMinRole'))->toBeTrue();
});

test('is owner returns true for owner role', function () {
    $user = User::factory()->create(['role' => 'owner']);

    expect($user->isOwner())->toBeTrue();
});

test('is owner returns false for manager', function () {
    $user = User::factory()->create(['role' => 'manager']);

    expect($user->isOwner())->toBeFalse();
});

test('is owner returns false for staff', function () {
    $user = User::factory()->create(['role' => 'staff']);

    expect($user->isOwner())->toBeFalse();
});

test('is manager returns true for manager', function () {
    $user = User::factory()->create(['role' => 'manager']);

    expect($user->isManager())->toBeTrue();
});

test('is staff returns true for staff', function () {
    $user = User::factory()->create(['role' => 'staff']);

    expect($user->isStaff())->toBeTrue();
});

test('has min role staff is true for all roles', function () {
    foreach (['staff', 'manager', 'owner'] as $role) {
        $user = User::factory()->create(['role' => $role]);
        expect($user->hasMinRole('staff'))->toBeTrue("{$role} should have min role staff");
    }
});

test('has min role manager is false for staff', function () {
    $user = User::factory()->create(['role' => 'staff']);

    expect($user->hasMinRole('manager'))->toBeFalse();
});

test('has min role manager is true for manager and owner', function () {
    foreach (['manager', 'owner'] as $role) {
        $user = User::factory()->create(['role' => $role]);
        expect($user->hasMinRole('manager'))->toBeTrue("{$role} should have min role manager");
    }
});

test('has min role owner is true only for owner', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $manager = User::factory()->create(['role' => 'manager']);
    $staff = User::factory()->create(['role' => 'staff']);

    expect($owner->hasMinRole('owner'))->toBeTrue();
    expect($manager->hasMinRole('owner'))->toBeFalse();
    expect($staff->hasMinRole('owner'))->toBeFalse();
});
