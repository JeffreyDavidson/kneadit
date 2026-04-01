<?php

use App\Enums\Platform\SubscriptionTier;

test('tier levels are ordered correctly', function () {
    expect(SubscriptionTier::Starter->level())->toBe(1)
        ->and(SubscriptionTier::Growth->level())->toBe(2)
        ->and(SubscriptionTier::Pro->level())->toBe(3);
});

test('meetsRequirement checks tier hierarchy', function () {
    expect(SubscriptionTier::Pro->meetsRequirement(SubscriptionTier::Starter))->toBeTrue()
        ->and(SubscriptionTier::Pro->meetsRequirement(SubscriptionTier::Pro))->toBeTrue()
        ->and(SubscriptionTier::Starter->meetsRequirement(SubscriptionTier::Growth))->toBeFalse()
        ->and(SubscriptionTier::Growth->meetsRequirement(SubscriptionTier::Pro))->toBeFalse();
});
