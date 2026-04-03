<?php

namespace App\DataTransferObjects\Settings;

final readonly class OnboardingSettings
{
    public function __construct(
        public ?string $completedAt,
    ) {}
}
