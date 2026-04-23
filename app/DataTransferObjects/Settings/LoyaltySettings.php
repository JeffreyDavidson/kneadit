<?php

namespace App\DataTransferObjects\Settings;

final readonly class LoyaltySettings
{
    public function __construct(
        public bool $enabled,
        public int $pointsPerDollar,
        public string $programName,
        public bool $tiersEnabled = false,
        public int $tierSilverThreshold = 500,
        public int $tierGoldThreshold = 2000,
        public int $tierPlatinumThreshold = 5000,
        public bool $tierPerksEnabled = false,
        public float $tierSilverMultiplier = 1.0,
        public bool $tierSilverFreeDelivery = false,
        public float $tierGoldMultiplier = 1.5,
        public bool $tierGoldFreeDelivery = true,
        public float $tierPlatinumMultiplier = 2.0,
        public bool $tierPlatinumFreeDelivery = true,
    ) {}

    public static function resolve(): self
    {
        return new self(
            enabled: settings('loyalty_enabled', '1') === '1',
            pointsPerDollar: (int) settings('loyalty_points_per_dollar', '10'),
            programName: (string) settings('loyalty_program_name', 'Rewards'),
            tiersEnabled: settings('loyalty_tiers_enabled', '0') === '1',
            tierSilverThreshold: (int) settings('loyalty_tier_silver_threshold', '500'),
            tierGoldThreshold: (int) settings('loyalty_tier_gold_threshold', '2000'),
            tierPlatinumThreshold: (int) settings('loyalty_tier_platinum_threshold', '5000'),
            tierPerksEnabled: settings('loyalty_tier_perks_enabled', '0') === '1',
            tierSilverMultiplier: (float) settings('loyalty_tier_silver_multiplier', '1.0'),
            tierSilverFreeDelivery: settings('loyalty_tier_silver_free_delivery', '0') === '1',
            tierGoldMultiplier: (float) settings('loyalty_tier_gold_multiplier', '1.5'),
            tierGoldFreeDelivery: settings('loyalty_tier_gold_free_delivery', '1') === '1',
            tierPlatinumMultiplier: (float) settings('loyalty_tier_platinum_multiplier', '2.0'),
            tierPlatinumFreeDelivery: settings('loyalty_tier_platinum_free_delivery', '1') === '1',
        );
    }
}
