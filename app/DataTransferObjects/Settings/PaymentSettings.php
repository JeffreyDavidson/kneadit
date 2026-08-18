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
            methodsAccepted: array_values(array_filter(
                SettingValue::decodedList(settings('payment_methods_accepted')),
                fn (mixed $method): bool => is_string($method),
            )),
        );
    }
}
