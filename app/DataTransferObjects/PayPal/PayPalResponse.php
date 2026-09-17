<?php

declare(strict_types=1);

namespace App\DataTransferObjects\PayPal;

final readonly class PayPalResponse
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public bool $successful,
        public int $status,
        public array $data = [],
    ) {}

}
