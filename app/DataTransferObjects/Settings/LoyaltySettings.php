<?php

namespace App\DataTransferObjects\Settings;

final readonly class LoyaltySettings
{
    public function __construct(
        public bool $enabled,
        public int $pointsPerDollar,
        public string $programName,
    ) {}

    public static function resolve(): self
    {
        return new self(
            enabled: settings('loyalty_enabled', '1') === '1',
            pointsPerDollar: (int) settings('loyalty_points_per_dollar', '10'),
            programName: (string) settings('loyalty_program_name', 'Rewards'),
        );
    }
}
