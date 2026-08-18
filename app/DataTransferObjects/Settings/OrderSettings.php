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
            leadTimeHours: (int) settings('order_lead_time_hours', '24'),
            deliveryEnabled: settings('delivery_enabled', '1') === '1',
            freeDeliveryMinimum: (string) settings('free_delivery_minimum', '50'),
            minimumPickupOrderAmount: (string) settings('minimum_pickup_order_amount', '0'),
            minimumDeliveryOrderAmount: (string) settings('minimum_delivery_order_amount', '0'),
            deliveryFeeTiers: (array) json_decode((string) settings('delivery_fee_tiers', '[]'), true),
            defaultDailyCapacity: (int) settings('default_daily_capacity', '20'),
            modificationWindowMinutes: (int) settings('order_modification_window_minutes', '0'),
            pickupSlotsEnabled: settings('pickup_slots_enabled', '0') === '1',
            pickupSlotIntervalMinutes: (int) settings('pickup_slot_interval_minutes', '30'),
            pickupSlotMaxPerWindow: (int) settings('pickup_slot_max_per_window', '3'),
            sitewideSaleEnabled: settings('sitewide_sale_enabled', '0') === '1',
            sitewideSalePercent: (int) settings('sitewide_sale_percent', '0'),
            sitewideSaleLabel: (string) settings('sitewide_sale_label', 'Sale'),
        );
    }

    public function leadTimeDays(): int
    {
        return (int) ceil($this->leadTimeHours / 24);
    }
}
