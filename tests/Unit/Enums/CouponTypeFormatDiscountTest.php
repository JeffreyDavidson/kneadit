<?php

use App\Enums\CouponType;

test('percentage type formats as percent off', function () {
    expect(CouponType::Percentage->formatDiscount(10))->toBe('10% off');
});

test('fixed type formats as currency off', function () {
    expect(CouponType::Fixed->formatDiscount(5.00))->toBe('$5.00 off');
});
