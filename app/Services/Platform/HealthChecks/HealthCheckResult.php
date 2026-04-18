<?php

namespace App\Services\Platform\HealthChecks;

final readonly class HealthCheckResult
{
    private function __construct(
        public bool $passed,
        public string $message,
    ) {}

    public static function pass(string $message): self
    {
        return new self(true, $message);
    }

    public static function fail(string $message): self
    {
        return new self(false, $message);
    }
}
