<?php

use App\Enums\Orders\PaymentStatus;

test('PaymentStatus has a color for every case', function (PaymentStatus $case) {
    expect($case->getColor())->toBeString();
})->with(PaymentStatus::cases());

test('PaymentStatus knows which cases can be marked paid', function (PaymentStatus $case, bool $expected) {
    expect($case->canBeMarkedPaid())->toBe($expected);
})->with([
    'unpaid' => [PaymentStatus::Unpaid, true],
    'partial' => [PaymentStatus::Partial, true],
    'paid' => [PaymentStatus::Paid, false],
    'cancelled' => [PaymentStatus::Cancelled, false],
    'refunded' => [PaymentStatus::Refunded, false],
]);

test('PaymentStatus knows which cases can still earn loyalty points', function (PaymentStatus $case, bool $expected) {
    expect($case->earnsLoyaltyPoints())->toBe($expected);
})->with([
    'unpaid' => [PaymentStatus::Unpaid, true],
    'partial' => [PaymentStatus::Partial, true],
    'paid' => [PaymentStatus::Paid, true],
    'cancelled' => [PaymentStatus::Cancelled, false],
    'refunded' => [PaymentStatus::Refunded, false],
]);
