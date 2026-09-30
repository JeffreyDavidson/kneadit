<?php

namespace App\DataTransferObjects\Settings;

use App\Enums\Orders\PaymentMethod;

final readonly class PaymentSettings
{
    /**
     * @param  array<int, string>  $methodsAccepted
     */
    public function __construct(
        public array $methodsAccepted,
    ) {}

    public static function resolve(): self
    {
        // The settings page and onboarding save payment_methods; payment_methods_accepted is the older seeded/imported key.
        $methods = SettingValue::stringList(settings('payment_methods'));

        if ($methods === []) {
            $methods = SettingValue::stringList(settings('payment_methods_accepted'));
        }

        return new self(
            methodsAccepted: array_values(array_filter(
                $methods,
                fn (string $method): bool => PaymentMethod::tryFrom($method) !== null,
            )),
        );
    }
}
