<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Mail\OrderConfirmedMail;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('transitions order from pending to confirmed', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    $result = resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    expect($result->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('throws exception for invalid transition', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Ready);
})->throws(InvalidOrderTransitionException::class, 'Cannot change status from pending to ready');

test('rejects transitions from terminal delivered state', function () {
    $order = Order::factory()->delivered()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);
})->throws(InvalidOrderTransitionException::class);

test('sends status email on transition', function () {
    $customer = Customer::query()->create(['name' => 'Test', 'email' => 'test@example.com']);
    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
        'customer_id' => $customer->id,
    ]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    Mail::assertQueued(OrderConfirmedMail::class);
});

test('allowedTransitions returns valid next statuses', function () {
    $pending = Order::factory()->create(['status' => OrderStatus::Pending]);
    $confirmed = Order::factory()->create(['status' => OrderStatus::Confirmed]);
    $delivered = Order::factory()->delivered()->create();

    expect(TransitionOrderStatus::allowedTransitions($pending))
        ->toContain(OrderStatus::Confirmed)
        ->toContain(OrderStatus::Cancelled)
        ->and(TransitionOrderStatus::allowedTransitions($confirmed))
        ->toContain(OrderStatus::Baking)
        ->and(TransitionOrderStatus::allowedTransitions($delivered))
        ->toBeEmpty();
});
