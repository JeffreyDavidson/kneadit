<?php

namespace App\DataTransferObjects\Settings;

final readonly class PolicySettings
{
    public function __construct(
        public bool $showOnStorefront,
        public string $cancellation,
        public string $deposit,
        public string $refund,
        public string $pickup,
        public string $additionalTerms,
    ) {}
}
