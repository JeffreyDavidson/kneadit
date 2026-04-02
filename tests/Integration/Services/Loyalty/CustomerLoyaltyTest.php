<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Services\Loyalty\CustomerLoyalty;
use App\ValueObjects\LoyaltyBalance;

beforeEach(function () {
    setUpTenantTest();

    $this->customer = Customer::factory()->create();
});

test('balance returns a LoyaltyBalance value object', function () {
    LoyaltyPoint::factory()->earned(100)->for($this->customer)->create();
    LoyaltyPoint::factory()->earned(50)->for($this->customer)->create();
    LoyaltyPoint::factory()->redeemed(30)->for($this->customer)->create();
    LoyaltyPoint::factory()->adjusted(10)->for($this->customer)->create();

    $balance = resolve(CustomerLoyalty::class)->balance($this->customer);

    expect($balance)
        ->toBeInstanceOf(LoyaltyBalance::class)
        ->earned->toBe(150)
        ->redeemed->toBe(30)
        ->adjusted->toBe(10)
        ->total->toBe(130);
});

test('balance returns zeros when customer has no points', function () {
    $balance = resolve(CustomerLoyalty::class)->balance($this->customer);

    expect($balance)
        ->earned->toBe(0)
        ->redeemed->toBe(0)
        ->adjusted->toBe(0)
        ->total->toBe(0);
});

test('snapshot returns balance and history', function () {
    LoyaltyPoint::factory()->earned(100)->for($this->customer)->create();
    LoyaltyPoint::factory()->redeemed(30)->for($this->customer)->create();

    $snapshot = resolve(CustomerLoyalty::class)->snapshot($this->customer);

    expect($snapshot['balance'])
        ->toBeInstanceOf(LoyaltyBalance::class)
        ->total->toBe(70);

    expect($snapshot['history'])->toHaveCount(2);
});

test('snapshot respects history limit', function () {
    LoyaltyPoint::factory()->earned(10)->for($this->customer)->count(5)->create();

    $snapshot = resolve(CustomerLoyalty::class)->snapshot($this->customer, historyLimit: 3);

    expect($snapshot['history'])->toHaveCount(3);
});
