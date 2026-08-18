<?php

use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function policyViewAny(string $policyClass, User $user): bool
{
    $policy = new $policyClass;

    if (! method_exists($policy, 'viewAny')) {
        throw new LogicException("Policy {$policyClass} does not define viewAny.");
    }

    $result = $policy->viewAny($user);

    if (! is_bool($result)) {
        throw new LogicException("Policy {$policyClass}::viewAny did not return a boolean.");
    }

    return $result;
}

test('manager policies deny staff users viewAny', function (string $policyClass) {
    $staff = User::factory()->staff()->create();

    expect(policyViewAny($policyClass, $staff))->toBeFalse();
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

    expect(policyViewAny($policyClass, $manager))->toBeTrue();
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

    expect(policyViewAny($policyClass, $staff))->toBeTrue();
})->with([
    'OrderPolicy' => [App\Policies\Orders\OrderPolicy::class],
    'ProductPolicy' => [App\Policies\Inventory\ProductPolicy::class],
]);
