<?php

use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('role is cast to UserRole enum', function () {
    $user = User::factory()->owner()->create();

    expect($user->role)->toBe(UserRole::Owner);
});

test('is_owner returns true for owner role', function () {
    $user = User::factory()->owner()->create();

    expect($user->is_owner)->toBeTrue();
});

test('is_owner returns false for manager', function () {
    $user = User::factory()->manager()->create();

    expect($user->is_owner)->toBeFalse();
});

test('is_owner returns false for staff', function () {
    $user = User::factory()->staff()->create();

    expect($user->is_owner)->toBeFalse();
});

test('is_manager returns true for manager', function () {
    $user = User::factory()->manager()->create();

    expect($user->is_manager)->toBeTrue();
});

test('is_staff returns true for staff', function () {
    $user = User::factory()->staff()->create();

    expect($user->is_staff)->toBeTrue();
});

test('role meetsRequirement staff is true for all roles', function (string $roleMethod) {
    $user = User::factory()->$roleMethod()->create();

    expect($user->role->meetsRequirement(UserRole::Staff))->toBeTrue();
})->with(['staff', 'manager', 'owner']);

test('role meetsRequirement manager is false for staff', function () {
    $user = User::factory()->staff()->create();

    expect($user->role->meetsRequirement(UserRole::Manager))->toBeFalse();
});

test('role meetsRequirement manager is true for manager and owner', function (string $roleMethod) {
    $user = User::factory()->$roleMethod()->create();

    expect($user->role->meetsRequirement(UserRole::Manager))->toBeTrue();
})->with(['manager', 'owner']);

test('role meetsRequirement owner is true only for owner', function () {
    $owner = User::factory()->owner()->create();
    $manager = User::factory()->manager()->create();
    $staff = User::factory()->staff()->create();

    expect($owner->role->meetsRequirement(UserRole::Owner))->toBeTrue()
        ->and($manager->role->meetsRequirement(UserRole::Owner))->toBeFalse()
        ->and($staff->role->meetsRequirement(UserRole::Owner))->toBeFalse();
});
