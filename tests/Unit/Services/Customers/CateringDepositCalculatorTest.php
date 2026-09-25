<?php

use App\Models\Customers\CateringInquiry;
use App\Services\Customers\CateringDepositCalculator;

test('calculates the configured percentage of the quoted amount', function () {
    $inquiry = new CateringInquiry(['quoted_amount' => 800.00]);

    expect(resolve(CateringDepositCalculator::class)->suggestedAmount($inquiry, 25))
        ->toBe(200.00);
});

test('caps the suggested deposit percentage at the full quote', function () {
    $inquiry = new CateringInquiry(['quoted_amount' => 800.00]);

    expect(resolve(CateringDepositCalculator::class)->suggestedAmount($inquiry, 125))
        ->toBe(800.00);
});

test('rounds the suggested deposit to cents', function () {
    $inquiry = new CateringInquiry(['quoted_amount' => 80.05]);

    expect(resolve(CateringDepositCalculator::class)->suggestedAmount($inquiry, 25))
        ->toBe(20.01);
});

test('returns zero when there is no quote or the percentage is not positive', function () {
    $calculator = resolve(CateringDepositCalculator::class);

    expect($calculator->suggestedAmount(new CateringInquiry, 25))->toBe(0.0)
        ->and($calculator->suggestedAmount(new CateringInquiry(['quoted_amount' => 800.00]), 0))->toBe(0.0);
});
