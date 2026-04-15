<?php

namespace App\DataTransferObjects\Settings;

final readonly class LoyaltySettings
{
    public function __construct(
        public bool $enabled,
        public int $pointsPerDollar,
        public string $programName,
    ) {}
}
