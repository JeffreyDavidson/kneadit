<?php

use App\DataTransferObjects\Customers\CustomerMetrics;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

test('can be constructed with all properties', function () {
    $metrics = new CustomerMetrics(
        lifetimeValue: 250.00,
        orderCount: 5,
        averageOrderValue: 50.00,
        lastOrderDate: Date::parse('2026-03-01'),
        daysSinceLastOrder: 23,
        isAtRisk: false,
        totalPoints: 400,
        lifetimePointsEarned: 500,
    );

    expect($metrics)
        ->lifetimeValue->toBe(250.00)
        ->orderCount->toBe(5)
        ->averageOrderValue->toBe(50.00)
        ->lastOrderDate->toBeInstanceOf(Carbon::class)
        ->daysSinceLastOrder->toBe(23)
        ->isAtRisk->toBeFalse()
        ->totalPoints->toBe(400)
        ->lifetimePointsEarned->toBe(500);
});
