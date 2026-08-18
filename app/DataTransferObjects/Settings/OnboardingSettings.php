<?php

namespace App\DataTransferObjects\Settings;

final readonly class OnboardingSettings
{
    public function __construct(
        public ?string $completedAt,
    ) {}

    public static function resolve(): self
    {
        return new self(
            completedAt: settings('onboarding_completed_at'),
        );
    }
}
