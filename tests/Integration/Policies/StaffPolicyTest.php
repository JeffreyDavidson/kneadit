<?php

use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function staffPolicyResult(string $policyClass, string $ability, User $user, ?object $model = null): bool
{
    $policy = new $policyClass;

    if (! method_exists($policy, $ability)) {
        throw new LogicException("Policy {$policyClass} does not define {$ability}.");
    }

    $result = $model === null
        ? $policy->{$ability}($user)
        : $policy->{$ability}($user, $model);

    if (! is_bool($result)) {
        throw new LogicException("Policy {$policyClass}::{$ability} did not return a boolean.");
    }

    return $result;
}

dataset('staffPolicies', [
    'BlogPost' => [App\Policies\Content\BlogPostPolicy::class, App\Models\Content\BlogPost::class],
    'Order' => [App\Policies\Orders\OrderPolicy::class, App\Models\Orders\Order::class],
    'Product' => [App\Policies\Inventory\ProductPolicy::class, App\Models\Inventory\Product::class],
]);

test('staff policies allow staff users', function (string $policyClass, string $modelClass) {
    $user = User::factory()->staff()->create();
    $model = new $modelClass;

    expect(staffPolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
        ->and(staffPolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
        ->and(staffPolicyResult($policyClass, 'create', $user))->toBeTrue()
        ->and(staffPolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
        ->and(staffPolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
})->with('staffPolicies');

test('staff policies allow owner users', function (string $policyClass, string $modelClass) {
    $user = User::factory()->owner()->create();
    $model = new $modelClass;

    expect(staffPolicyResult($policyClass, 'viewAny', $user))->toBeTrue()
        ->and(staffPolicyResult($policyClass, 'view', $user, $model))->toBeTrue()
        ->and(staffPolicyResult($policyClass, 'create', $user))->toBeTrue()
        ->and(staffPolicyResult($policyClass, 'update', $user, $model))->toBeTrue()
        ->and(staffPolicyResult($policyClass, 'delete', $user, $model))->toBeTrue();
})->with('staffPolicies');
