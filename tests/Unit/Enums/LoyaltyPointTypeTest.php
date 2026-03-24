<?php

use App\Enums\LoyaltyPointType;

test('has expected cases', function () {
    expect(LoyaltyPointType::cases())
        ->toHaveCount(3)
        ->and(LoyaltyPointType::Earned->value)->toBe('earned')
        ->and(LoyaltyPointType::Redeemed->value)->toBe('redeemed')
        ->and(LoyaltyPointType::Adjusted->value)->toBe('adjusted');
});
