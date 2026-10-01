<?php

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Policies\Orders\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('deletableOrderStates', [
    'pending and unpaid' => [OrderStatus::Pending, PaymentStatus::Unpaid],
    'cancelled and unpaid' => [OrderStatus::Cancelled, PaymentStatus::Unpaid],
    'cancelled with cancelled payment' => [OrderStatus::Cancelled, PaymentStatus::Cancelled],
]);

dataset('undeletableOrderStates', [
    'pending but paid' => [OrderStatus::Pending, PaymentStatus::Paid],
    'pending but partially paid' => [OrderStatus::Pending, PaymentStatus::Partial],
    'cancelled but refunded' => [OrderStatus::Cancelled, PaymentStatus::Refunded],
    'confirmed' => [OrderStatus::Confirmed, PaymentStatus::Unpaid],
    'baking' => [OrderStatus::Baking, PaymentStatus::Paid],
    'ready' => [OrderStatus::Ready, PaymentStatus::Paid],
    'delivered' => [OrderStatus::Delivered, PaymentStatus::Paid],
    'delivered and refunded' => [OrderStatus::Delivered, PaymentStatus::Refunded],
]);

dataset('deleteCapableRoles', ['manager', 'owner']);

test('staff can view, create and update orders', function () {
    $staff = User::factory()->staff()->create();
    $order = Order::factory()->create();

    $policy = new OrderPolicy;

    expect($policy->viewAny($staff))->toBeTrue()
        ->and($policy->view($staff, $order))->toBeTrue()
        ->and($policy->create($staff))->toBeTrue()
        ->and($policy->update($staff, $order))->toBeTrue();
});

test('staff can never delete an order', function (OrderStatus $status, PaymentStatus $paymentStatus) {
    $staff = User::factory()->staff()->create();
    $order = Order::factory()->create(['status' => $status, 'payment_status' => $paymentStatus]);

    expect((new OrderPolicy)->delete($staff, $order))->toBeFalse();
})->with('deletableOrderStates');

test('manager and owner can delete an order nothing financial happened on', function (string $role, OrderStatus $status, PaymentStatus $paymentStatus) {
    $user = User::factory()->{$role}()->create();
    $order = Order::factory()->create(['status' => $status, 'payment_status' => $paymentStatus]);

    expect((new OrderPolicy)->delete($user, $order))->toBeTrue();
})->with('deleteCapableRoles')->with('deletableOrderStates');

test('nobody can delete an order that was progressed or paid', function (string $role, OrderStatus $status, PaymentStatus $paymentStatus) {
    $user = User::factory()->{$role}()->create();
    $order = Order::factory()->create(['status' => $status, 'payment_status' => $paymentStatus]);

    expect((new OrderPolicy)->delete($user, $order))->toBeFalse();
})->with('deleteCapableRoles')->with('undeletableOrderStates');

test('nobody can delete an order that has a refund', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $order = Order::factory()->cancelled()->create(['payment_status' => PaymentStatus::Cancelled]);
    Refund::factory()->for($order)->create();

    expect((new OrderPolicy)->delete($user, $order))->toBeFalse();
})->with('deleteCapableRoles');
