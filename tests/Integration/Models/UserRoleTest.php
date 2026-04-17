<?php

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('role is cast to UserRole enum', function () {
    $user = User::factory()->create(['role' => UserRole::Owner]);

    expect($user->role)->toBe(UserRole::Owner);
});

test('is_owner returns true for owner role', function () {
    $user = User::factory()->create(['role' => UserRole::Owner]);

    expect($user->is_owner)->toBeTrue();
});

test('is_owner returns false for manager', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);

    expect($user->is_owner)->toBeFalse();
});

test('is_owner returns false for staff', function () {
    $user = User::factory()->create(['role' => UserRole::Staff]);

    expect($user->is_owner)->toBeFalse();
});

test('is_manager returns true for manager', function () {
    $user = User::factory()->create(['role' => UserRole::Manager]);

    expect($user->is_manager)->toBeTrue();
});

test('is_staff returns true for staff', function () {
    $user = User::factory()->create(['role' => UserRole::Staff]);

    expect($user->is_staff)->toBeTrue();
});

test('role meetsRequirement staff is true for all roles', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    expect($user->role->meetsRequirement(UserRole::Staff))->toBeTrue();
})->with(['staff', 'manager', 'owner']);

test('role meetsRequirement manager is false for staff', function () {
    $user = User::factory()->create(['role' => UserRole::Staff]);

    expect($user->role->meetsRequirement(UserRole::Manager))->toBeFalse();
});

test('role meetsRequirement manager is true for manager and owner', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    expect($user->role->meetsRequirement(UserRole::Manager))->toBeTrue();
})->with(['manager', 'owner']);

test('role meetsRequirement owner is true only for owner', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $manager = User::factory()->create(['role' => UserRole::Manager]);
    $staff = User::factory()->create(['role' => UserRole::Staff]);

    expect($owner->role->meetsRequirement(UserRole::Owner))->toBeTrue()
        ->and($manager->role->meetsRequirement(UserRole::Owner))->toBeFalse()
        ->and($staff->role->meetsRequirement(UserRole::Owner))->toBeFalse();
});
