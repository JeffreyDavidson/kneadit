<?php

use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('manager policies deny staff users viewAny', function (string $policyClass) {
    $staff = User::factory()->staff()->create();

    expect((new $policyClass)->viewAny($staff))->toBeFalse();
})->with([
    'BlockedDatePolicy' => [App\Policies\Operations\BlockedDatePolicy::class],
    'CapacityLimitPolicy' => [App\Policies\Operations\CapacityLimitPolicy::class],
    'CategoryPolicy' => [App\Policies\Inventory\CategoryPolicy::class],
    'CouponPolicy' => [App\Policies\Engagement\CouponPolicy::class],
    'CustomerPolicy' => [App\Policies\Customers\CustomerPolicy::class],
    'ExpensePolicy' => [App\Policies\Financial\ExpensePolicy::class],
    'GiftCardPolicy' => [App\Policies\Financial\GiftCardPolicy::class],
    'IncomePolicy' => [App\Policies\Financial\IncomePolicy::class],
    'IngredientPolicy' => [App\Policies\Inventory\IngredientPolicy::class],
    'ReviewPolicy' => [App\Policies\Customers\ReviewPolicy::class],
    'SettingPolicy' => [App\Policies\Operations\SettingPolicy::class],
    'SupplierPolicy' => [App\Policies\Inventory\SupplierPolicy::class],
]);

test('manager policies allow manager users viewAny', function (string $policyClass) {
    $manager = User::factory()->manager()->create();

    expect((new $policyClass)->viewAny($manager))->toBeTrue();
})->with([
    'BlockedDatePolicy' => [App\Policies\Operations\BlockedDatePolicy::class],
    'CapacityLimitPolicy' => [App\Policies\Operations\CapacityLimitPolicy::class],
    'CategoryPolicy' => [App\Policies\Inventory\CategoryPolicy::class],
    'CouponPolicy' => [App\Policies\Engagement\CouponPolicy::class],
    'CustomerPolicy' => [App\Policies\Customers\CustomerPolicy::class],
    'ExpensePolicy' => [App\Policies\Financial\ExpensePolicy::class],
    'GiftCardPolicy' => [App\Policies\Financial\GiftCardPolicy::class],
    'IncomePolicy' => [App\Policies\Financial\IncomePolicy::class],
    'IngredientPolicy' => [App\Policies\Inventory\IngredientPolicy::class],
    'ReviewPolicy' => [App\Policies\Customers\ReviewPolicy::class],
    'SettingPolicy' => [App\Policies\Operations\SettingPolicy::class],
    'SupplierPolicy' => [App\Policies\Inventory\SupplierPolicy::class],
]);

test('staff-level policies allow staff users viewAny', function (string $policyClass) {
    $staff = User::factory()->staff()->create();

    expect((new $policyClass)->viewAny($staff))->toBeTrue();
})->with([
    'OrderPolicy' => [App\Policies\Orders\OrderPolicy::class],
    'ProductPolicy' => [App\Policies\Inventory\ProductPolicy::class],
]);
