<?php

use App\Enums\Platform\SubscriptionTier;

test('it resolves a tier from a stripe price id', function () {
    config(['kneadit.stripe_prices' => [
        'starter' => 'price_starter_123',
        'growth' => 'price_growth_456',
        'pro' => 'price_pro_789',
    ]]);

    expect(SubscriptionTier::fromPriceId('price_starter_123'))->toBe(SubscriptionTier::Starter)
        ->and(SubscriptionTier::fromPriceId('price_growth_456'))->toBe(SubscriptionTier::Growth)
        ->and(SubscriptionTier::fromPriceId('price_pro_789'))->toBe(SubscriptionTier::Pro);
});

test('it returns null for unknown price id', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter_123']]);

    expect(SubscriptionTier::fromPriceId('price_unknown'))->toBeNull();
});
