<?php

use App\Enums\Platform\SubscriptionTier;
use App\Models\Staff\User;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;

beforeEach(fn () => setUpCentralTest());

test('can access central panel only as platform admin', function () {
    $admin = User::factory()->platformAdmin()->createOne();
    $owner = User::factory()->owner()->createOne();

    $panel = Panel::make()->id('central');

    expect($admin->canAccessPanel($panel))->toBeTrue()
        ->and($owner->canAccessPanel($panel))->toBeFalse();
});

test('can access non-central panel for any role', function (string $factoryState) {
    $user = match ($factoryState) {
        'owner' => User::factory()->owner()->createOne(),
        'manager' => User::factory()->manager()->createOne(),
        'staff' => User::factory()->staff()->createOne(),
        default => throw new InvalidArgumentException("Unknown factory state: {$factoryState}"),
    };

    $panel = Panel::make()->id('app');

    expect($user->canAccessPanel($panel))->toBeTrue();
})->with(['owner', 'manager', 'staff']);

test('SubscriptionTier::resolve returns null when no subscription exists', function () {
    $user = User::factory()->owner()->create();

    expect(SubscriptionTier::resolve($user))->toBeNull();
});

test('has-plan gate returns false when no subscription exists', function () {
    $user = User::factory()->owner()->create();

    expect(Gate::forUser($user)->allows('has-plan', SubscriptionTier::Starter))->toBeFalse();
});

test('SubscriptionTier::resolve returns tier matching stripe price', function () {
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

    expect(SubscriptionTier::resolve($user))->toBe(SubscriptionTier::Starter);
});

test('SubscriptionTier::resolve returns null for unknown stripe price', function () {
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

    expect(SubscriptionTier::resolve($user))->toBeNull();
});

test('SubscriptionTier::resolve returns Pro for users whose tenant has free_forever', function () {
    $user = User::factory()->owner()->create();
    createTenant(['id' => 'free-tenant', 'user_id' => $user->id, 'free_forever' => true]);

    expect(SubscriptionTier::resolve($user))->toBe(SubscriptionTier::Pro);
});

test('has-plan gate passes every tier for free-forever users', function () {
    $user = User::factory()->owner()->create();
    createTenant(['id' => 'free-tenant-gate', 'user_id' => $user->id, 'free_forever' => true]);

    expect(Gate::forUser($user)->allows('has-plan', SubscriptionTier::Pro))->toBeTrue()
        ->and(Gate::forUser($user)->allows('has-plan', SubscriptionTier::Growth))->toBeTrue()
        ->and(Gate::forUser($user)->allows('has-plan', SubscriptionTier::Starter))->toBeTrue();
});

test('tenants relationship returns related tenants', function () {
    $user = User::factory()->owner()->create();

    createTenant(['id' => 'user-tenant-1', 'email' => 'user1@test.com']);

    Illuminate\Support\Facades\DB::table('tenants')
        ->where('id', 'user-tenant-1')
        ->update(['user_id' => $user->id]);

    expect($user->tenants)->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
});
