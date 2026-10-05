<?php

declare(strict_types=1);

namespace App\ValueObjects;

final readonly class LoyaltyBalance
{
    public int $total;

    /** Earned points still standing: what refunds took back no longer counts toward tiers. */
    public int $netEarned;

    public function __construct(
        public int $earned,
        public int $redeemed,
        public int $adjusted,
        public int $reversed = 0,
    ) {
        $this->netEarned = $this->earned - $this->reversed;
        $this->total = $this->earned + $this->adjusted - $this->redeemed - $this->reversed;
    }
}
