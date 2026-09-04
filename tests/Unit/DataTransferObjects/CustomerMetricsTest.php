<?php

use App\DataTransferObjects\Customers\CustomerMetrics;
use App\ValueObjects\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

test('can be constructed with all properties', function () {
    $metrics = new CustomerMetrics(
        lifetimeValue: Money::fromDollars(250),
        orderCount: 5,
        averageOrderValue: Money::fromDollars(50),
        lastOrderDate: Date::parse('2026-03-01'),
        daysSinceLastOrder: 23,
        isAtRisk: false,
        totalPoints: 400,
        lifetimePointsEarned: 500,
    );

    expect($metrics)
        ->lifetimeValue->toEqual(Money::fromDollars(250))
        ->orderCount->toBe(5)
        ->averageOrderValue->toEqual(Money::fromDollars(50))
        ->lastOrderDate->toBeInstanceOf(Carbon::class)
        ->daysSinceLastOrder->toBe(23)
        ->isAtRisk->toBeFalse()
        ->totalPoints->toBe(400)
        ->lifetimePointsEarned->toBe(500);
});
