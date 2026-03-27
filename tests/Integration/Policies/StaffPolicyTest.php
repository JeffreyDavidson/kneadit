<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('staffPolicies', [
    'BlogPost' => ['App\Policies\BlogPostPolicy', 'App\Models\BlogPost'],
    'Order' => ['App\Policies\OrderPolicy', 'App\Models\Order'],
    'Product' => ['App\Policies\ProductPolicy', 'App\Models\Product'],
]);

test('staff policies allow staff users', function (string $policyClass, string $modelClass) {
    $policy = new $policyClass;
    $user = User::factory()->staff()->create();
    $model = new $modelClass;

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user, $model))->toBeTrue()
        ->and($policy->create($user))->toBeTrue()
        ->and($policy->update($user, $model))->toBeTrue()
        ->and($policy->delete($user, $model))->toBeTrue();
})->with('staffPolicies');

test('staff policies allow owner users', function (string $policyClass, string $modelClass) {
    $policy = new $policyClass;
    $user = User::factory()->owner()->create();
    $model = new $modelClass;

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user, $model))->toBeTrue()
        ->and($policy->create($user))->toBeTrue()
        ->and($policy->update($user, $model))->toBeTrue()
        ->and($policy->delete($user, $model))->toBeTrue();
})->with('staffPolicies');
