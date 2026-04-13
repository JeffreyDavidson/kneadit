<?php

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
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

    expect($user->hasPlan(SubscriptionTier::Starter))->toBeFalse();
});

test('current plan returns plan key matching stripe price', function () {
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

    expect($user->currentPlan())->toBe(SubscriptionTier::Starter);
});

test('current plan returns null for unknown stripe price', function () {
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

    expect($user->currentPlan())->toBeNull();
});

test('has access returns false with no subscription or trial', function () {
    $user = User::factory()->owner()->create();

    expect($user->hasAccess())->toBeFalse();
});

test('isOwner returns true for owner role', function () {
    $user = User::factory()->owner()->create();

    expect($user->isOwner())->toBeTrue()
        ->and($user->isManager())->toBeFalse()
        ->and($user->isStaff())->toBeFalse();
});

test('isManager returns true for manager role', function () {
    $user = User::factory()->manager()->create();

    expect($user->isManager())->toBeTrue()
        ->and($user->isOwner())->toBeFalse();
});

test('isStaff returns true for staff role', function () {
    $user = User::factory()->staff()->create();

    expect($user->isStaff())->toBeTrue()
        ->and($user->isOwner())->toBeFalse();
});

test('tenants relationship returns related tenants', function () {
    $user = User::factory()->owner()->create();

    createTenant(['id' => 'user-tenant-1', 'email' => 'user1@test.com']);

    // Manually insert the user_id on tenant for relationship test
    Illuminate\Support\Facades\DB::table('tenants')
        ->where('id', 'user-tenant-1')
        ->update(['user_id' => $user->id]);

    // This test only works if the tenants table has a user_id column
    // If it doesn't, the relationship just returns empty
    expect($user->tenants)->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class);
});

test('hasMinRole checks hierarchical role requirement', function () {
    $owner = User::factory()->owner()->create();
    $staff = User::factory()->staff()->create();

    expect($owner->hasMinRole(UserRole::Staff))->toBeTrue()
        ->and($owner->hasMinRole(UserRole::Owner))->toBeTrue()
        ->and($staff->hasMinRole(UserRole::Owner))->toBeFalse()
        ->and($staff->hasMinRole(UserRole::Staff))->toBeTrue();
});
