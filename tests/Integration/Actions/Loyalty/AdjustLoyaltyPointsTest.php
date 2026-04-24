<?php

use App\Actions\Loyalty\AdjustLoyaltyPoints;
use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;

beforeEach(fn () => setUpTenantTest());

test('creates adjusted loyalty point record', function () {
    $customer = Customer::factory()->create();

    $point = resolve(AdjustLoyaltyPoints::class)($customer, 50, 'Manual bonus');

    expect($point)
        ->customer_id->toBe($customer->id)
        ->points->toBe(50)
        ->type->toBe(LoyaltyPointType::Adjusted)
        ->description->toBe('Manual bonus');
});

test('supports negative point adjustments', function () {
    $customer = Customer::factory()->create();

    $point = resolve(AdjustLoyaltyPoints::class)($customer, -25, 'Correction');

    expect($point->points)->toBe(-25);
});
