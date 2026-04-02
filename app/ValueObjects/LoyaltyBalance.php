<?php

namespace App\ValueObjects;

final readonly class LoyaltyBalance
{
    public int $total;

    public function __construct(
        public int $earned,
        public int $redeemed,
        public int $adjusted,
    ) {
        $this->total = $this->earned + $this->adjusted - $this->redeemed;
    }
}
