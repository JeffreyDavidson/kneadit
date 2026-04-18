<?php

use App\Enums\Platform\SubscriptionTier;
use App\Models\Staff\User;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;

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

test('current_plan returns null when no subscription exists', function () {
    $user = User::factory()->owner()->create();

    expect($user->current_plan)->toBeNull();
});

test('has-plan gate returns false when no subscription exists', function () {
    $user = User::factory()->owner()->create();

    expect(Gate::forUser($user)->allows('has-plan', SubscriptionTier::Starter))->toBeFalse();
});

test('current_plan returns plan key matching stripe price', function () {
    config(['kneadit.stripe_prices.starter' => 'price_starter_test']);

    $user = User::factory()->owner()->create();

    Illuminate\Support\Facades\DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_starter_test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($user->current_plan)->toBe(SubscriptionTier::Starter);
});

test('current_plan returns null for unknown stripe price', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_known']]);

    $user = User::factory()->owner()->create();

    Illuminate\Support\Facades\DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_456',
        'stripe_status' => 'active',
        'stripe_price' => 'price_unknown',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect($user->current_plan)->toBeNull();
});

test('is_owner returns true for owner role', function () {
    $user = User::factory()->owner()->create();

    expect($user->is_owner)->toBeTrue()
        ->and($user->is_manager)->toBeFalse()
        ->and($user->is_staff)->toBeFalse();
});

test('is_manager returns true for manager role', function () {
    $user = User::factory()->manager()->create();

    expect($user->is_manager)->toBeTrue()
        ->and($user->is_owner)->toBeFalse();
});

test('is_staff returns true for staff role', function () {
    $user = User::factory()->staff()->create();

    expect($user->is_staff)->toBeTrue()
        ->and($user->is_owner)->toBeFalse();
});

test('tenants relationship returns related tenants', function () {
    $user = User::factory()->owner()->create();

    createTenant(['id' => 'user-tenant-1', 'email' => 'user1@test.com']);

    Illuminate\Support\Facades\DB::table('tenants')
        ->where('id', 'user-tenant-1')
        ->update(['user_id' => $user->id]);

    expect($user->tenants)->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
});
