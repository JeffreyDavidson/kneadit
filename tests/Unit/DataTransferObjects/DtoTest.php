<?php

use App\DataTransferObjects\CouponValidationResult;
use App\DataTransferObjects\CreateGiftCardData;
use App\DataTransferObjects\CreateOrderData;
use App\DataTransferObjects\GiftCardRedemptionResult;

test('CreateOrderData::fromArray maps validated request data', function () {
    $dto = CreateOrderData::fromArray([
        'customer_name' => 'Jane',
        'customer_email' => 'jane@example.com',
        'delivery_date' => '2026-04-01',
        'delivery_type' => 'pickup',
        'items' => [['product_id' => 1, 'quantity' => 2]],
        'coupon_id' => '5',
    ]);

    expect($dto->customerName)->toBe('Jane')
        ->and($dto->items)->toHaveCount(1)
        ->and($dto->couponId)->toBe(5)
        ->and($dto->notes)->toBeNull();
});

test('CouponValidationResult named constructors', function () {
    $invalid = CouponValidationResult::invalid('Not found');

    expect($invalid->valid)->toBeFalse()
        ->and($invalid->error)->toBe('Not found')
        ->and($invalid->coupon)->toBeNull();
});

test('GiftCardRedemptionResult named constructors', function () {
    $redeemed = GiftCardRedemptionResult::redeemed(25.00, 75.00);
    $failed = GiftCardRedemptionResult::failed('Expired');

    expect($redeemed->success)->toBeTrue()
        ->and($redeemed->amountApplied)->toBe(25.00)
        ->and($redeemed->remainingBalance)->toBe(75.00)->and($failed->success)->toBeFalse()->and($failed->error)->toBe('Expired');
});

test('CreateGiftCardData::fromArray maps purchase data', function () {
    $dto = CreateGiftCardData::fromArray([
        'purchaser_name' => 'Jane',
        'purchaser_email' => 'jane@example.com',
        'initial_balance' => 50,
    ]);

    expect($dto->purchaserName)->toBe('Jane')
        ->and($dto->initialBalance)->toBe(50.0)
        ->and($dto->recipientName)->toBeNull();
});
