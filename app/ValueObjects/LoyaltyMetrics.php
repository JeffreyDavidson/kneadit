<?php

namespace App\ValueObjects;

final readonly class LoyaltyMetrics
{
    public function __construct(
        public int $totalIssued,
        public int $totalRedeemed,
        public int $activeMembers,
        public int $availableRewards,
    ) {}
}
