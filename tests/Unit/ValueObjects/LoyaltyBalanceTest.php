<?php

use App\ValueObjects\LoyaltyBalance;

test('total is earned plus adjusted minus redeemed', function () {
    $balance = new LoyaltyBalance(earned: 100, redeemed: 30, adjusted: 10);

    expect($balance->total)->toBe(80);
});

test('total is zero when no points', function () {
    $balance = new LoyaltyBalance(earned: 0, redeemed: 0, adjusted: 0);

    expect($balance->total)->toBe(0);
});

test('total can be negative when redeemed exceeds earned', function () {
    $balance = new LoyaltyBalance(earned: 50, redeemed: 100, adjusted: 0);

    expect($balance->total)->toBe(-50);
});
