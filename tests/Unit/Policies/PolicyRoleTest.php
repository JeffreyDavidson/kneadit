<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('manager policies deny staff users viewAny', function (string $policyClass) {
    $staff = User::factory()->staff()->create();

    expect((new $policyClass)->viewAny($staff))->toBeFalse();
})->with([
    'BlockedDatePolicy' => [App\Policies\BlockedDatePolicy::class],
    'CapacityLimitPolicy' => [App\Policies\CapacityLimitPolicy::class],
    'CategoryPolicy' => [App\Policies\CategoryPolicy::class],
    'CouponPolicy' => [App\Policies\CouponPolicy::class],
    'CustomerPolicy' => [App\Policies\CustomerPolicy::class],
    'ExpensePolicy' => [App\Policies\ExpensePolicy::class],
    'GiftCardPolicy' => [App\Policies\GiftCardPolicy::class],
    'IncomePolicy' => [App\Policies\IncomePolicy::class],
    'IngredientPolicy' => [App\Policies\IngredientPolicy::class],
    'ReviewPolicy' => [App\Policies\ReviewPolicy::class],
    'SettingPolicy' => [App\Policies\SettingPolicy::class],
    'SupplierPolicy' => [App\Policies\SupplierPolicy::class],
]);

test('manager policies allow manager users viewAny', function (string $policyClass) {
    $manager = User::factory()->manager()->create();

    expect((new $policyClass)->viewAny($manager))->toBeTrue();
})->with([
    'BlockedDatePolicy' => [App\Policies\BlockedDatePolicy::class],
    'CapacityLimitPolicy' => [App\Policies\CapacityLimitPolicy::class],
    'CategoryPolicy' => [App\Policies\CategoryPolicy::class],
    'CouponPolicy' => [App\Policies\CouponPolicy::class],
    'CustomerPolicy' => [App\Policies\CustomerPolicy::class],
    'ExpensePolicy' => [App\Policies\ExpensePolicy::class],
    'GiftCardPolicy' => [App\Policies\GiftCardPolicy::class],
    'IncomePolicy' => [App\Policies\IncomePolicy::class],
    'IngredientPolicy' => [App\Policies\IngredientPolicy::class],
    'ReviewPolicy' => [App\Policies\ReviewPolicy::class],
    'SettingPolicy' => [App\Policies\SettingPolicy::class],
    'SupplierPolicy' => [App\Policies\SupplierPolicy::class],
]);

test('staff-level policies allow staff users viewAny', function (string $policyClass) {
    $staff = User::factory()->staff()->create();

    expect((new $policyClass)->viewAny($staff))->toBeTrue();
})->with([
    'OrderPolicy' => [App\Policies\OrderPolicy::class],
    'ProductPolicy' => [App\Policies\ProductPolicy::class],
]);
