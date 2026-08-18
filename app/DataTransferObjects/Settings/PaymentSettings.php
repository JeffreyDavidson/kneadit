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

    public static function resolve(): self
    {
        return new self(
            methodsAccepted: (array) json_decode((string) settings('payment_methods_accepted', '[]'), true),
        );
    }
}
