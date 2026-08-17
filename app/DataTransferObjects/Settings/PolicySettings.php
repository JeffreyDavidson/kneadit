<?php

namespace App\DataTransferObjects\Settings;

final readonly class PolicySettings
{
    public function __construct(
        public bool $showOnStorefront,
        public string $cancellation,
        public string $deposit,
        public string $refund,
        public string $pickup,
        public string $additionalTerms,
    ) {}

    public static function resolve(): self
    {
        return new self(
            showOnStorefront: settings('show_policies_on_storefront', '0') === '1',
            cancellation: SettingValue::string(settings('cancellation_policy')),
            deposit: SettingValue::string(settings('deposit_policy')),
            refund: SettingValue::string(settings('refund_policy')),
            pickup: SettingValue::string(settings('pickup_policy')),
            additionalTerms: SettingValue::string(settings('additional_terms')),
        );
    }
}
