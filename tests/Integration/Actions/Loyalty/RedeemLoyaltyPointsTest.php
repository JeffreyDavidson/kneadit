<?php

use App\Actions\Loyalty\RedeemLoyaltyPoints;
use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;

beforeEach(fn () => setUpTenantTest());

test('creates redeemed loyalty point record', function () {
    $customer = Customer::factory()->create();

    $point = app(RedeemLoyaltyPoints::class)($customer, 100, 'Free Cookie reward');

    expect($point)
        ->customer_id->toBe($customer->id)
        ->points->toBe(100)
        ->type->toBe(LoyaltyPointType::Redeemed)
        ->description->toBe('Free Cookie reward');
});
