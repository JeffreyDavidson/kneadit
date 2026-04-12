<?php

use App\Models\Orders\Order;
use App\Models\Orders\OrderMessage;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
});

test('order message belongs to order', function () {
    $order = Order::factory()->recycle(test()->user)->create();
    $message = OrderMessage::factory()->recycle($order)->create();

    expect($message->order)->toBeInstanceOf(Order::class)
        ->and($message->order->id)->toBe($order->id);
});

test('isBaker returns true for baker messages', function () {
    $message = OrderMessage::factory()->fromBaker()->recycle(
        Order::factory()->recycle(test()->user)->create(),
    )->create();

    expect($message->isBaker())->toBeTrue()
        ->and($message->isCustomer())->toBeFalse();
});

test('isCustomer returns true for customer messages', function () {
    $message = OrderMessage::factory()->fromCustomer()->recycle(
        Order::factory()->recycle(test()->user)->create(),
    )->create();

    expect($message->isCustomer())->toBeTrue()
        ->and($message->isBaker())->toBeFalse();
});
