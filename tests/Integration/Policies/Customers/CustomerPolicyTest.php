<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Policies\Customers\CustomerPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('managerRoles', ['manager', 'owner']);

test('managers and owners can manage a customer with no orders', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $customer = Customer::factory()->create();

    $policy = new CustomerPolicy;

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->view($user, $customer))->toBeTrue()
        ->and($policy->create($user))->toBeTrue()
        ->and($policy->update($user, $customer))->toBeTrue()
        ->and($policy->delete($user, $customer))->toBeTrue();
})->with('managerRoles');

test('nobody can delete a customer who has orders', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->create();

    expect((new CustomerPolicy)->delete($user, $customer))->toBeFalse();
})->with('managerRoles');

test('staff cannot manage customers', function () {
    $staff = User::factory()->staff()->create();
    $customer = Customer::factory()->create();

    $policy = new CustomerPolicy;

    expect($policy->viewAny($staff))->toBeFalse()
        ->and($policy->view($staff, $customer))->toBeFalse()
        ->and($policy->create($staff))->toBeFalse()
        ->and($policy->update($staff, $customer))->toBeFalse()
        ->and($policy->delete($staff, $customer))->toBeFalse();
});
