<?php

use App\Models\Staff\User;
use Filament\Panel;

beforeEach(fn () => setUpCentralTest());

test('can access central panel only as platform admin', function () {
    $admin = User::factory()->platformAdmin()->create();
    $owner = User::factory()->owner()->create();

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('central');

    expect($admin->canAccessPanel($panel))->toBeTrue()
        ->and($owner->canAccessPanel($panel))->toBeFalse();
});

test('can access non-central panel for any role', function (string $factoryState) {
    $user = User::factory()->{$factoryState}()->create();

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('app');

    expect($user->canAccessPanel($panel))->toBeTrue();
})->with(['owner', 'manager', 'staff']);

test('current plan returns null when no subscription exists', function () {
    $user = User::factory()->owner()->create();

    expect($user->currentPlan())->toBeNull();
});

test('has plan returns false when no subscription exists', function () {
    $user = User::factory()->owner()->create();

    expect($user->hasPlan('starter'))->toBeFalse();
});

test('has plan returns false for invalid plan name', function () {
    $user = User::factory()->owner()->create();

    expect($user->hasPlan('nonexistent'))->toBeFalse();
});
