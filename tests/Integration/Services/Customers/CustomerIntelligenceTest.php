<?php

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Services\Customers\CustomerIntelligence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('metrics returns correct values for customer with orders', function () {
    $customer = Customer::factory()->create();

    Order::factory()->count(3)->create([
        'customer_id' => $customer->id,
        'status' => OrderStatus::Delivered,
        'payment_status' => PaymentStatus::Paid,
        'total' => 50.00,
        'subtotal' => 50.00,
        'delivery_date' => now()->subDays(5),
    ]);

    $metrics = resolve(CustomerIntelligence::class)->metrics($customer);

    expect($metrics)
        ->lifetimeValue->toBe(150.00)
        ->orderCount->toBe(3)
        ->averageOrderValue->toBe(50.00)
        ->lastOrderDate->not->toBeNull()
        ->isAtRisk->toBeFalse();
});

test('metrics returns safe defaults for customer with no orders', function () {
    $customer = Customer::factory()->create();

    $metrics = resolve(CustomerIntelligence::class)->metrics($customer);

    expect($metrics)
        ->lifetimeValue->toBe(0.0)
        ->orderCount->toBe(0)
        ->averageOrderValue->toBe(0.0)
        ->lastOrderDate->toBeNull()
        ->daysSinceLastOrder->toBeNull()
        ->isAtRisk->toBeFalse()
        ->totalPoints->toBe(0)
        ->lifetimePointsEarned->toBe(0);
});

test('at-risk threshold is configurable via setting', function () {
    $customer = Customer::factory()->create();

    Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => OrderStatus::Delivered,
        'total' => 25.00,
        'subtotal' => 25.00,
        'created_at' => now()->subDays(45),
    ]);

    config(['analytics.at_risk_threshold_days' => 30]);
    $metrics = resolve(CustomerIntelligence::class)->metrics($customer);
    expect($metrics->isAtRisk)->toBeTrue();

    config(['analytics.at_risk_threshold_days' => 60]);
    $metrics = resolve(CustomerIntelligence::class)->metrics($customer);
    expect($metrics->isAtRisk)->toBeFalse();
});

test('metrics calculates loyalty points correctly', function () {
    $customer = Customer::factory()->create();

    LoyaltyPoint::factory()->earned(500)->for($customer)->create();
    LoyaltyPoint::factory()->redeemed(100)->for($customer)->create();
    LoyaltyPoint::factory()->adjusted(50)->for($customer)->create();

    $metrics = resolve(CustomerIntelligence::class)->metrics($customer);

    expect($metrics)
        ->totalPoints->toBe(450)
        ->lifetimePointsEarned->toBe(500);
});
