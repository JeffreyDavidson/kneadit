<?php

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('user model has role methods', function () {
    $user = new User;

    expect(method_exists($user, 'isOwner'))->toBeTrue()->and(method_exists($user, 'isManager'))->toBeTrue()->and(method_exists($user, 'isStaff'))->toBeTrue()->and(method_exists($user, 'hasMinRole'))->toBeTrue();
});

test('role is cast to UserRole enum', function () {
    $user = User::factory()->create(['role' => UserRole::Owner]);

    expect($user->role)->toBe(UserRole::Owner);
});

test('is owner returns true for owner role', function () {
    $user = User::factory()->create(['role' => UserRole::Owner]);

    expect($user->isOwner())->toBeTrue();
});

test('is owner returns false for manager', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);

    expect($user->isOwner())->toBeFalse();
});

test('is owner returns false for staff', function () {
    $user = User::factory()->create(['role' => UserRole::Staff]);

    expect($user->isOwner())->toBeFalse();
});

test('is manager returns true for manager', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);

    expect($user->isManager())->toBeTrue();
});

test('is staff returns true for staff', function () {
    $user = User::factory()->create(['role' => UserRole::Staff]);

    expect($user->isStaff())->toBeTrue();
});

test('has min role staff is true for all roles', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    expect($user->hasMinRole(UserRole::Staff))->toBeTrue();
})->with(['staff', 'manager', 'owner']);

test('has min role manager is false for staff', function () {
    $user = User::factory()->create(['role' => UserRole::Staff]);

    expect($user->hasMinRole(UserRole::Manager))->toBeFalse();
});

test('has min role manager is true for manager and owner', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    expect($user->hasMinRole(UserRole::Manager))->toBeTrue();
})->with(['manager', 'owner']);

test('has min role owner is true only for owner', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    $staff = User::factory()->create(['role' => UserRole::Staff]);

    expect($owner->hasMinRole(UserRole::Owner))->toBeTrue()->and($manager->hasMinRole(UserRole::Owner))->toBeFalse()->and($staff->hasMinRole(UserRole::Owner))->toBeFalse();
});
