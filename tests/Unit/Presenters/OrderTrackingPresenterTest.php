<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use App\Presenters\OrderTrackingPresenter;

function makePresenter(OrderStatus $status): OrderTrackingPresenter
{
    $order = new Order;
    $order->status = $status;

    return new OrderTrackingPresenter($order, OrderStatus::trackableStatuses());
}

test('cancelled order is flagged as cancelled', function () {
    $presenter = makePresenter(OrderStatus::Cancelled);

    expect($presenter->isCancelled)->toBeTrue();
});

test('non-cancelled order is not flagged', function () {
    $presenter = makePresenter(OrderStatus::Pending);

    expect($presenter->isCancelled)->toBeFalse();
});

test('current step index matches status position in trackable list', function (OrderStatus $status, int $expectedIndex) {
    $presenter = makePresenter($status);

    expect($presenter->currentStepIndex)->toBe($expectedIndex);
})->with([
    'pending' => [OrderStatus::Pending, 0],
    'confirmed' => [OrderStatus::Confirmed, 1],
    'baking' => [OrderStatus::Baking, 2],
    'ready' => [OrderStatus::Ready, 3],
    'delivered' => [OrderStatus::Delivered, 4],
]);

test('cancelled order has step index of -1', function () {
    $presenter = makePresenter(OrderStatus::Cancelled);

    expect($presenter->currentStepIndex)->toBe(-1);
});

test('isStepCompleted returns true for steps at or before current', function () {
    $presenter = makePresenter(OrderStatus::Baking);

    expect($presenter->isStepCompleted(0))->toBeTrue()
        ->and($presenter->isStepCompleted(1))->toBeTrue()
        ->and($presenter->isStepCompleted(2))->toBeTrue()
        ->and($presenter->isStepCompleted(3))->toBeFalse()
        ->and($presenter->isStepCompleted(4))->toBeFalse();
});

test('isCurrentStep returns true only for the current step', function () {
    $presenter = makePresenter(OrderStatus::Confirmed);

    expect($presenter->isCurrentStep(0))->toBeFalse()
        ->and($presenter->isCurrentStep(1))->toBeTrue()
        ->and($presenter->isCurrentStep(2))->toBeFalse();
});

test('progressPercentage returns correct value', function (OrderStatus $status, float $expected) {
    $presenter = makePresenter($status);

    expect($presenter->progressPercentage())->toBe($expected);
})->with([
    'pending (0%)' => [OrderStatus::Pending, 0.0],
    'confirmed (25%)' => [OrderStatus::Confirmed, 25.0],
    'baking (50%)' => [OrderStatus::Baking, 50.0],
    'ready (75%)' => [OrderStatus::Ready, 75.0],
    'delivered (100%)' => [OrderStatus::Delivered, 100.0],
]);
