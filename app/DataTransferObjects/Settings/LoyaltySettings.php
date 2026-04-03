<?php

namespace App\DataTransferObjects\Settings;

final readonly class LoyaltySettings
{
    public function __construct(
        public bool $enabled,
        public string $pointsPerDollar,
        public string $programName,
    ) {}
}
