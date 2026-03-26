<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Services\Customer\CustomerIntelligence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

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

    LoyaltyPoint::query()->create(['customer_id' => $customer->id, 'points' => 500, 'type' => 'earned', 'description' => 'test']);
    LoyaltyPoint::query()->create(['customer_id' => $customer->id, 'points' => 100, 'type' => 'redeemed', 'description' => 'test']);
    LoyaltyPoint::query()->create(['customer_id' => $customer->id, 'points' => 50, 'type' => 'adjusted', 'description' => 'test']);

    $metrics = resolve(CustomerIntelligence::class)->metrics($customer);

    expect($metrics)
        ->totalPoints->toBe(450)
        ->lifetimePointsEarned->toBe(500);
});
