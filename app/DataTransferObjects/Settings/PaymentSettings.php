<?php

namespace App\DataTransferObjects\Settings;

final readonly class PaymentSettings
{
    /**
     * @param array<int, string> $methodsAccepted
     */
    public function __construct(
        public array $methodsAccepted,
    ) {}
}
