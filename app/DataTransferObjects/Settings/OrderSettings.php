<?php

namespace App\DataTransferObjects\Settings;

final readonly class OrderSettings
{
    /**
     * @param array<int, array<string, mixed>> $deliveryFeeTiers
     */
    public function __construct(
        public int $leadTimeHours,
        public bool $deliveryEnabled,
        public string $freeDeliveryMinimum,
        public string $minimumPickupOrderAmount,
        public string $minimumDeliveryOrderAmount,
        public array $deliveryFeeTiers,
        public int $defaultDailyCapacity,
        public int $modificationWindowMinutes = 0,
        public bool $pickupSlotsEnabled = false,
        public int $pickupSlotIntervalMinutes = 30,
        public int $pickupSlotMaxPerWindow = 3,
        public bool $sitewideSaleEnabled = false,
        public int $sitewideSalePercent = 0,
        public string $sitewideSaleLabel = 'Sale',
    ) {}

    public static function resolve(): self
    {
        return new self(
            leadTimeHours: SettingValue::int(settings('order_lead_time_hours'), 24),
            deliveryEnabled: settings('delivery_enabled', '1') === '1',
            freeDeliveryMinimum: SettingValue::string(settings('free_delivery_minimum'), '50'),
            minimumPickupOrderAmount: SettingValue::string(settings('minimum_pickup_order_amount'), '0'),
            minimumDeliveryOrderAmount: SettingValue::string(settings('minimum_delivery_order_amount'), '0'),
            deliveryFeeTiers: self::resolveDeliveryFeeTiers(),
            defaultDailyCapacity: SettingValue::int(settings('default_daily_capacity'), 20),
            modificationWindowMinutes: SettingValue::int(settings('order_modification_window_minutes'), 0),
            pickupSlotsEnabled: settings('pickup_slots_enabled', '0') === '1',
            pickupSlotIntervalMinutes: SettingValue::int(settings('pickup_slot_interval_minutes'), 30),
            pickupSlotMaxPerWindow: SettingValue::int(settings('pickup_slot_max_per_window'), 3),
            sitewideSaleEnabled: settings('sitewide_sale_enabled', '0') === '1',
            sitewideSalePercent: SettingValue::int(settings('sitewide_sale_percent'), 0),
            sitewideSaleLabel: SettingValue::string(settings('sitewide_sale_label'), 'Sale'),
        );
    }

    /** @return array<int, array<string, mixed>> */
    private static function resolveDeliveryFeeTiers(): array
    {
        $tiers = [];

        foreach (SettingValue::decodedList(settings('delivery_fee_tiers')) as $tier) {
            if (is_array($tier)) {
                $tiers[] = SettingValue::map($tier);
            }
        }

        return $tiers;
    }

    public function leadTimeDays(): int
    {
        return (int) ceil($this->leadTimeHours / 24);
    }
}
