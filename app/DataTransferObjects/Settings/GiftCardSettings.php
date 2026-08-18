<?php

namespace App\DataTransferObjects\Settings;

final readonly class GiftCardSettings
{
    /**
     * @param array<int, int> $presetAmounts
     */
    public function __construct(
        public array $presetAmounts,
        public int $defaultAmount,
    ) {}

    public static function resolve(): self
    {
        return new self(
            presetAmounts: array_map(
                'intval',
                array_filter(explode(',', SettingValue::string(settings('gift_card_preset_amounts'), '10,25,50,100'))),
            ),
            defaultAmount: SettingValue::int(settings('gift_card_default_amount'), 25),
        );
    }
}
